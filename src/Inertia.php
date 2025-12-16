<?php

namespace Core;

class Inertia
{
    protected static $rootView = 'app';
    protected static $sharedProps = [];

    public static function share($key, $value = null)
    {
        if (is_array($key)) {
            self::$sharedProps = array_merge(self::$sharedProps, $key);
        } else {
            self::$sharedProps[$key] = $value;
        }
    }

    public static function render($component, $props = [])
    {
        $props = array_merge(self::$sharedProps, $props);
        
        $page = [
            'component' => $component,
            'props' => $props,
            'url' => $_SERVER['REQUEST_URI'] ?? '/',
            'version' => '1.0.0' // Asset versioning
        ];

        // 1. Inertia İsteği Kontrolü (AJAX)
        if (isset($_SERVER['HTTP_X_INERTIA'])) {
            header('Content-Type: application/json');
            header('X-Inertia: true');
            header('Vary: Accept');
            echo json_encode($page);
            exit;
        }

        // 2. İlk Ziyaret (HTML Render)
        return self::renderHtml($page);
    }

    protected static function renderHtml($page)
    {
        $json = json_encode($page);
        $inertiaDiv = "<div id='app' data-page='" . htmlspecialchars($json, ENT_QUOTES, 'UTF-8') . "'></div>";
        
        $templatePath = __DIR__ . '/../resources/views/' . self::$rootView . '.php';
        
        // DEBUG
        // dd('Inertia Render Path Check', $templatePath, file_exists($templatePath));

        if (!file_exists($templatePath)) {
            throw new \Exception("View file not found: {$templatePath}");
        }

        $content = file_get_contents($templatePath);

        // --- Template Engine ---

        // 1. @inertia
        $content = str_replace('@inertiaHead', '', $content);
        $content = str_replace('@inertia', $inertiaDiv, $content);

        // 2. @viteReactRefresh (Sadece Dev Ortamında Çalışmalı)
        $isDev = self::isDev();
        $reactRefresh = '';
        
        if ($isDev) {
            $viteUrl = self::getViteUrl();
            $reactRefresh = '
            <script type="module">
                import RefreshRuntime from "' . $viteUrl . '/@react-refresh"
                RefreshRuntime.injectIntoGlobalHook(window)
                window.$RefreshReg$ = () => {}
                window.$RefreshSig$ = () => (type) => type
                window.__vite_plugin_react_preamble_installed__ = true
            </script>';
        }
        $content = str_replace('@viteReactRefresh', $reactRefresh, $content);

        // 3. @vite(['...']) parser improved for multiple files
        $content = preg_replace_callback('/@vite\(\[(.*?)\]\)/', function($matches) {
            $rawInput = $matches[1];
            // Temizleme: tırnakları ve boşlukları kaldırıp array'e çevir
            $files = array_map(function($file) {
                return trim($file, "'\" ");
            }, explode(',', $rawInput));

            $tags = '';
            
            if (self::isDev()) {
                $viteUrl = self::getViteUrl();
                $tags .= '<script type="module" src="' . $viteUrl . '/@vite/client"></script>';
                foreach ($files as $file) {
                    $tags .= '<script type="module" src="' . $viteUrl . '/' . $file . '"></script>';
                }
            } else {
                // Production Logic (Manifest Loop)
                foreach ($files as $file) {
                    $tags .= self::resolveUsingManifest($file);
                }
            }
            return $tags;
        }, $content);

        return $content;
    }

    protected static function isDev()
    {
        // Basit bir kontrol: public/hot dosyası varsa Dev modudur (Laravel tarzı)
        // Ya da yerel geliştirme ortamında manuel true dönebiliriz.
        // Şimdilik "hot" dosyasını simüle edemediğimiz için port kontrolü yapabilir 
        // veya varsayılan olarak dev kabul edip, file check ekleyebiliriz.
        // Doğrusu: file_exists public/hot
        return file_exists(__DIR__ . '/../public/hot');
    }

    protected static function detectEnvironment()
    {
        $hotPath = __DIR__ . '/../../public/hot';
        
        if (file_exists($hotPath)) {
            $url = trim(file_get_contents($hotPath));
            return ['env' => 'local', 'url' => $url];
        }

        return ['env' => 'production'];
    }

    protected static function getViteUrl()
    {
        $hotFile = __DIR__ . '/../public/hot';
        if (file_exists($hotFile)) {
            return trim(file_get_contents($hotFile));
        }
        return 'http://localhost:5173';
    }

    protected static function resolveUsingManifest($input)
    {
        // Production: Manifest.json oku
        $manifestPath = __DIR__ . '/../public/build/manifest.json';
        if (!file_exists($manifestPath)) {
            return "<!-- Build Manifest Not Found. Please run 'npm run build' -->";
        }

        $manifest = json_decode(file_get_contents($manifestPath), true);
        
        // Input (resources/js/app.jsx) manifestte var mı?
        if (isset($manifest[$input])) {
            $file = $manifest[$input]['file'];
            $cssTags = '';
            
            // CSS varsa ekle
            if (!empty($manifest[$input]['css'])) {
                foreach ($manifest[$input]['css'] as $css) {
                    $cssTags .= '<link rel="stylesheet" href="/build/' . $css . '">';
                }
            }

            return $cssTags . '<script type="module" src="/build/' . $file . '"></script>';
        }

        return "<!-- Asset not found in manifest: {$input} -->";
    }
}

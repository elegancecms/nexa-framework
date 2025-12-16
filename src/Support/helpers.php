<?php

if (!function_exists('dd')) {
    function dd(...$vars)
    {
        echo '<style>
            body { background: #1a202c; color: #cbd5e0; font-family: monospace; padding: 20px; }
            pre { background: #2d3748; padding: 15px; border-radius: 5px; border: 1px solid #4a5568; white-space: pre-wrap; }
            .type { color: #f6ad55; font-weight: bold; }
            .string { color: #68d391; }
            .int { color: #63b3ed; }
        </style>';

        foreach ($vars as $var) {
            echo '<pre>';
            var_dump_better($var);
            echo '</pre>';
        }
        die(1);
    }
}

if (!function_exists('dump')) {
    function dump(...$vars)
    {
        foreach ($vars as $var) {
            echo '<pre style="background: #f7fafc; padding: 10px; border: 1px solid #e2e8f0;">';
            var_dump($var);
            echo '</pre>';
        }
    }
}

function var_dump_better($var, $level = 0) {
    if (is_string($var)) {
        echo '<span class="type">string(' . strlen($var) . ')</span> <span class="string">"' . htmlspecialchars($var) . '"</span>';
    } elseif (is_int($var)) {
        echo '<span class="type">int</span> <span class="int">' . $var . '</span>';
    } elseif (is_bool($var)) {
        echo '<span class="type">bool</span> <span class="int">' . ($var ? 'true' : 'false') . '</span>';
    } elseif (is_null($var)) {
        echo '<span class="type">null</span>';
    } elseif (is_array($var)) {
        echo '<span class="type">array(' . count($var) . ')</span> [' . "\n";
        foreach ($var as $key => $value) {
            echo str_repeat("    ", $level + 1) . "['$key'] => ";
            var_dump_better($value, $level + 1);
            echo "\n";
        }
        echo str_repeat("    ", $level) . "]";
    } else {
        var_dump($var);
    }
}

<?php
$files = glob(__DIR__ . '/resources/views/livewire/*/*.blade.php');

foreach ($files as $file) {
    $content = file_get_contents($file);
    
    // Replace <div class="rounded-xl border border-neutral-200 p-4 dark:border-neutral-700">
    $content = preg_replace('/<div class="rounded-xl border border-neutral-200 p-4 dark:border-neutral-700">/', '<flux:card>', $content);
    
    // Replace <div class="rounded-xl border border-neutral-200 dark:border-neutral-700">
    $content = preg_replace('/<div class="rounded-xl border border-neutral-200 dark:border-neutral-700">/', '<flux:card class="!p-0">', $content);

    // Also the empty states:
    // <div class="rounded-xl border border-neutral-200 px-6 py-12 text-center dark:border-neutral-700">
    $content = preg_replace('/<div class="rounded-xl border border-neutral-200 px-6 py-12 text-center dark:border-neutral-700">/', '<flux:card class="text-center">', $content);

    // Let's replace the corresponding closing </div> for these cards. This is tricky with regex.
    // However, if we do it manually it might be better, or we can use a DOM parser. But since the structure is simple and consistent, we can just replace the outer most </div>?
    // Actually, maybe I should just manually do it or use regex if it's very consistent.
    // Instead of risking invalid HTML, let's just do it manually with multi_replace_file_content for the main ones.
}

<?php

require_once __DIR__ . '/../../vendor/Michelf/MarkdownExtra.inc.php';

function extractDocTitle(string $markdown, string $fallbackSlug): string
{
    $lines = explode("\n", str_replace(["\r\n", "\r"], "\n", $markdown));
    $i     = 0;
    $total = count($lines);

    while ($i < $total && trim($lines[$i]) === '') {
        $i++;
    }

    if ($i < $total) {
        $line   = trim($lines[$i]);
        $prefix = '<!-- title:';
        $suffix = '-->';

        if (
            strncmp($line, $prefix, strlen($prefix)) === 0 &&
            substr($line, -strlen($suffix)) === $suffix
        ) {
            $inner = substr($line, strlen($prefix), -strlen($suffix));
            $title = trim($inner, " \t\"'");

            if ($title !== '') {
                return $title;
            }
        }
    }

    return $fallbackSlug;
}

function stripDocTitle(string $markdown): string
{
    $lines = explode("\n", str_replace(["\r\n", "\r"], "\n", $markdown));
    $i     = 0;
    $total = count($lines);

    while ($i < $total && trim($lines[$i]) === '') {
        $i++;
    }

    if ($i < $total) {
        $line   = trim($lines[$i]);
        $prefix = '<!-- title:';
        $suffix = '-->';

        if (
            strncmp($line, $prefix, strlen($prefix)) === 0 &&
            substr($line, -strlen($suffix)) === $suffix
        ) {
            $rest = implode("\n", array_slice($lines, $i + 1));
            return ltrim($rest, "\n");
        }
    }

    return $markdown;
}

function loadDocs(string $docsDir): array
{
    $docs = [];

    foreach (scandir($docsDir) as $f) {
        if ($f === '' || $f[0] === '.') {
            continue;
        }
        $fullPath = $docsDir . '/' . $f;
        if (is_file($fullPath) && strtolower(pathinfo($f, PATHINFO_EXTENSION)) === 'md') {
            $slug  = pathinfo($f, PATHINFO_FILENAME);
            $raw   = (string) file_get_contents($fullPath);
            $title = extractDocTitle($raw, $slug);

            $docs[] = [
                'slug'  => $slug,
                'title' => $title,
            ];
        }
    }

    return $docs;
}

function sortDocs(array $docs): array
{
    usort($docs,
          static function (array $a, array $b): int {
                $cmp = strnatcasecmp($a['title'], $b['title']);
                return $cmp !== 0 ? $cmp : strnatcasecmp($a['slug'], $b['slug']);
            }
    );

    return $docs;
}

function resolveRequestedSlug(string $rawQuery): ?string
{
    $firstPart = explode('&', $rawQuery, 2)[0];

    if ($firstPart !== '' && strpos($firstPart, '=') === false) {
        $slug = basename(rawurldecode($firstPart));
        if ($slug !== '') {
            return $slug;
        }
    }

    return null;
}

function renderDocPage(string $docsDir, string $slug): string
{
    $mdFile = $docsDir . '/' . $slug . '.md';

    if (!is_file($mdFile)) {
        return '<p>Requested documentation page was not found.</p>';
    }

    $markdownText  = (string) file_get_contents($mdFile);
    $cleanMarkdown = stripDocTitle($markdownText);

    return Michelf\MarkdownExtra::defaultTransform($cleanMarkdown);
}

?>

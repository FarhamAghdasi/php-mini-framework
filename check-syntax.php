#!/usr/bin/env php
<?php
/**
 * PHP Syntax Checker for Mini Framework
 * Excludes vendor directory and other non-project files
 */

define('ROOT_PATH', __DIR__);

echo "🔍 PHP Syntax Checker - Mini Framework\n";
echo str_repeat("=", 70) . "\n";

// Function to check syntax of a single file
function checkFileSyntax(string $file): array
{
    $output = [];
    $status = 0;
    
    $command = 'php -l ' . escapeshellarg($file) . ' 2>&1';
    exec($command, $output, $status);
    
    // Filter out PHP version info
    $cleanOutput = array_filter($output, function($line) {
        return !preg_match('/^PHP\s+\d+\.\d+\.\d+/', $line);
    });
    
    return [
        'file' => $file,
        'valid' => $status === 0,
        'output' => implode("\n", $cleanOutput),
        'status' => $status
    ];
}

// Manual list of directories to scan (excluding vendor)
$directoriesToScan = [
    ROOT_PATH . '/app',
    ROOT_PATH . '/bootstrap',
    ROOT_PATH . '/config',
    ROOT_PATH . '/public',
    ROOT_PATH . '/resources',
];

// Manual list of root files to check
$rootFilesToCheck = [
    ROOT_PATH . '/index.php',
    ROOT_PATH . '/composer.json',
    ROOT_PATH . '/check-syntax.php', // خود اسکریپت
];

// Function to get PHP files from directory (excluding vendor)
function getPhpFilesFromDir(string $dir): array
{
    $files = [];
    
    if (!is_dir($dir)) {
        return $files;
    }
    
    $iterator = new DirectoryIterator($dir);
    
    foreach ($iterator as $fileInfo) {
        // Skip . and ..
        if ($fileInfo->isDot()) {
            continue;
        }
        
        $fullPath = $fileInfo->getRealPath();
        
        // Skip vendor directory
        if ($fileInfo->isDir() && $fileInfo->getFilename() === 'vendor') {
            continue;
        }
        
        if ($fileInfo->isDir()) {
            // Recursively scan subdirectories
            $subDirFiles = getPhpFilesFromDir($fullPath);
            $files = array_merge($files, $subDirFiles);
        } elseif ($fileInfo->isFile() && $fileInfo->getExtension() === 'php') {
            $files[] = $fullPath;
        }
    }
    
    return $files;
}

// Collect all files to check
$allFiles = [];

// Add root files
foreach ($rootFilesToCheck as $file) {
    if (file_exists($file)) {
        $allFiles[] = $file;
    }
}

// Add files from directories
foreach ($directoriesToScan as $dir) {
    if (is_dir($dir)) {
        $dirFiles = getPhpFilesFromDir($dir);
        $allFiles = array_merge($allFiles, $dirFiles);
    }
}

// Remove duplicates and sort
$allFiles = array_unique($allFiles);
sort($allFiles);

if (empty($allFiles)) {
    echo "❌ No PHP files found to check!\n";
    exit(1);
}

echo "📁 Found " . count($allFiles) . " PHP files to check (excluding vendor/)\n\n";

// Check each file
$results = [];
$validCount = 0;
$invalidCount = 0;

foreach ($allFiles as $file) {
    $result = checkFileSyntax($file);
    $results[] = $result;
    
    $relativePath = str_replace(ROOT_PATH . '/', '', $file);
    
    if ($result['valid']) {
        echo "✅ " . $relativePath . "\n";
        $validCount++;
    } else {
        echo "❌ " . $relativePath . "\n";
        // Show first error line
        $errorLines = explode("\n", $result['output']);
        if (!empty($errorLines[0])) {
            echo "   " . $errorLines[0] . "\n";
        }
        $invalidCount++;
    }
}

// Summary
echo "\n" . str_repeat("=", 70) . "\n";
echo "📊 CHECK COMPLETE\n";
echo str_repeat("-", 70) . "\n";
echo "Total files checked: " . count($allFiles) . "\n";
echo "✅ Valid: $validCount\n";
echo "❌ Invalid: $invalidCount\n";

if ($invalidCount > 0) {
    echo "\n🔍 ERRORS FOUND:\n";
    echo str_repeat("-", 70) . "\n";
    
    foreach ($results as $result) {
        if (!$result['valid'] && !empty($result['output'])) {
            $relativePath = str_replace(ROOT_PATH . '/', '', $result['file']);
            echo "\nFile: " . $relativePath . "\n";
            echo "Error: " . $result['output'] . "\n";
        }
    }
    
    echo "\n❌ Syntax check failed!\n";
    exit(1);
} else {
    echo "\n✅ All files passed syntax check!\n";
    
    // Quick structure check
    echo "\n🏗️  PROJECT STRUCTURE CHECK:\n";
    echo str_repeat("-", 70) . "\n";
    
    $requiredDirs = ['app', 'config', 'public'];
    $missingDirs = [];
    
    foreach ($requiredDirs as $dir) {
        if (!is_dir(ROOT_PATH . '/' . $dir)) {
            $missingDirs[] = $dir;
        }
    }
    
    if (empty($missingDirs)) {
        echo "✅ Required directories exist\n";
    } else {
        echo "⚠️  Missing directories: " . implode(', ', $missingDirs) . "\n";
    }
    
    // Check for .env
    if (!file_exists(ROOT_PATH . '/.env') && file_exists(ROOT_PATH . '/.env.example')) {
        echo "💡 Tip: Copy .env.example to .env\n";
    }
    
    // Check vendor
    if (!is_dir(ROOT_PATH . '/vendor')) {
        echo "💡 Tip: Run 'composer install' to install dependencies\n";
    }
    
    echo "\n✅ Project is ready!\n";
    exit(0);
}
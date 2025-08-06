<?php
/**
 * Simple PO to MO compiler
 */

if ($argc < 2) {
    echo "Usage: php compile-mo.php <input.po> [output.mo]\n";
    exit(1);
}

$inputFile = $argv[1];
$outputFile = $argv[2] ?? str_replace('.po', '.mo', $inputFile);

if (!file_exists($inputFile)) {
    echo "Error: Input file '$inputFile' not found\n";
    exit(1);
}

// Read PO file
$poContent = file_get_contents($inputFile);
$lines = explode("\n", $poContent);

$translations = [];
$currentMsgid = '';
$currentMsgstr = '';
$inMsgid = false;
$inMsgstr = false;

foreach ($lines as $line) {
    $line = trim($line);
    
    if (empty($line) || $line[0] === '#') {
        continue;
    }
    
    if (strpos($line, 'msgid ') === 0) {
        if ($inMsgstr) {
            $translations[$currentMsgid] = $currentMsgstr;
        }
        $currentMsgid = substr($line, 7, -1);
        $currentMsgstr = '';
        $inMsgid = true;
        $inMsgstr = false;
    } elseif (strpos($line, 'msgstr ') === 0) {
        $currentMsgstr = substr($line, 8, -1);
        $inMsgid = false;
        $inMsgstr = true;
    } elseif ($inMsgid && strpos($line, '"') === 0) {
        $currentMsgid .= substr($line, 1, -1);
    } elseif ($inMsgstr && strpos($line, '"') === 0) {
        $currentMsgstr .= substr($line, 1, -1);
    }
}

if ($inMsgstr) {
    $translations[$currentMsgid] = $currentMsgstr;
}

// Create MO file
$moData = '';
$moData .= pack('V', 0x950412de); // Magic number
$moData .= pack('V', 0); // Version
$moData .= pack('V', count($translations)); // Number of entries
$moData .= pack('V', 28); // Offset of table with original strings
$moData .= pack('V', 28 + 8 * count($translations)); // Offset of table with translation strings

$originalStrings = '';
$translationStrings = '';
$originalOffsets = [];
$translationOffsets = [];

foreach ($translations as $original => $translation) {
    $originalOffsets[] = [
        'length' => strlen($original),
        'offset' => strlen($originalStrings)
    ];
    $originalStrings .= $original . "\0";
    
    $translationOffsets[] = [
        'length' => strlen($translation),
        'offset' => strlen($translationStrings)
    ];
    $translationStrings .= $translation . "\0";
}

// Write original strings table
foreach ($originalOffsets as $offset) {
    $moData .= pack('V', $offset['length']);
    $moData .= pack('V', $offset['offset']);
}

// Write translation strings table
foreach ($translationOffsets as $offset) {
    $moData .= pack('V', $offset['length']);
    $moData .= pack('V', $offset['offset']);
}

$moData .= $originalStrings;
$moData .= $translationStrings;

file_put_contents($outputFile, $moData);

echo "Successfully compiled '$inputFile' to '$outputFile'\n";
echo "Total translations: " . count($translations) . "\n";
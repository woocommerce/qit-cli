<?php

/**
 * Add keys to snapshots without having to re-run all tests.
 */

// Define the keys to add and their values
$keysToAdd = [
	"test_variation" => [
		"after" => "phpstan_level",
		"value" => ""
	]
];

$snapshotsDir = 'tests/__snapshots__';

if (!is_dir($snapshotsDir)) {
    die("Snapshots directory not found: $snapshotsDir\n");
}

$snapshotFiles = glob("$snapshotsDir/*.php");

/**
 * Recursively inserts specified keys into the JSON structure.
 * 
 * @param array &$array The snapshot data array.
 * @param array $keysToAdd An array defining which key to add, after which key, and with what value.
 */
function insertKeys(&$array, $keysToAdd) {
    foreach ($array as &$entry) {
        if (is_array($entry)) {
            foreach ($keysToAdd as $newKey => $details) {
                $afterKey = $details['after']; // The key after which to insert
                $value = $details['value'];    // The value to assign

                if (array_key_exists($afterKey, $entry) && !array_key_exists($newKey, $entry)) {
                    echo "   ➡ Adding '$newKey' after '$afterKey' with value: " . json_encode($value) . "\n";

                    // Preserve order by inserting after the specified key
                    $orderedEntry = [];
                    foreach ($entry as $key => $val) {
                        $orderedEntry[$key] = $val;
                        if ($key === $afterKey) {
                            $orderedEntry[$newKey] = $value;
                        }
                    }
                    $entry = $orderedEntry;
                }
            }
            // Recurse into nested structures
            insertKeys($entry, $keysToAdd);
        }
    }
}

foreach ($snapshotFiles as $file) {
    echo "Processing: $file\n";

    $jsonString = require $file;

    if (!is_string($jsonString)) {
        echo "❌ Error: Expected a JSON string in $file, but got something else.\n";
        continue;
    }

    $data = json_decode($jsonString, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        echo "❌ Error decoding JSON in file: $file\n";
        echo "   ➡ " . json_last_error_msg() . "\n";
        continue;
    }

    // Insert keys recursively
    $before = json_encode($data, JSON_UNESCAPED_SLASHES);
    insertKeys($data, $keysToAdd);
    $after = json_encode($data, JSON_UNESCAPED_SLASHES);

    if ($before !== $after) {
        $newJsonString = json_encode($data, JSON_PRETTY_PRINT); // Keeps `\/` escaped
        $newPhpContent = "<?php return " . var_export($newJsonString, true) . ";\n";
        file_put_contents($file, $newPhpContent);
        echo "✅ Updated: $file\n";
    } else {
        echo "✔ No changes needed: $file\n";
    }
}

echo "🎉 Done.\n";


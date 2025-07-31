#!/bin/bash

# Script to concatenate all relevant QIT CLI files into a single text file
# Usage: ./concatenate_qit_files.sh [output_file]

# Set the base directory
BASE_DIR="/home/lucas/automattic/qit/qit-cli/src/src"
OUTPUT_FILE="${1:-qit_cli_concatenated.txt}"

# Check if base directory exists
if [ ! -d "$BASE_DIR" ]; then
    echo "Error: Base directory $BASE_DIR does not exist"
    exit 1
fi

# Create/clear the output file
> "$OUTPUT_FILE"

echo "Concatenating QIT CLI files into: $OUTPUT_FILE"
echo "========================================"

# Function to add a file with header
add_file() {
    local file="$1"
    local relative_path="${file#$BASE_DIR/}"

    if [ -f "$file" ]; then
        echo "" >> "$OUTPUT_FILE"
        echo "=================================================================================" >> "$OUTPUT_FILE"
        echo "FILE: $relative_path" >> "$OUTPUT_FILE"
        echo "=================================================================================" >> "$OUTPUT_FILE"
        echo "" >> "$OUTPUT_FILE"
        cat "$file" >> "$OUTPUT_FILE"
        echo "Added: $relative_path"
    else
        echo "Warning: File not found: $file"
    fi
}

# Add main command files
echo "Adding main command files..."
add_file "$BASE_DIR/Commands/QITCommand.php"
add_file "$BASE_DIR/Commands/DynamicCommand.php"
add_file "$BASE_DIR/Commands/DynamicCommandCreator.php"
add_file "$BASE_DIR/Commands/CreateRunCommands.php"
add_file "$BASE_DIR/Commands/RunActivationTestCommand.php"

# Add environment commands
echo "Adding environment commands..."
add_file "$BASE_DIR/Commands/Environment/UpEnvironmentCommand.php"

# Add custom test commands
echo "Adding custom test commands..."
add_file "$BASE_DIR/Commands/CustomTests/RunE2ECommand.php"

# Add all PreCommand namespace files
echo "Adding all PreCommand namespace files..."
find "$BASE_DIR/PreCommand" -name "*.php" -type f | sort | while read -r file; do
    add_file "$file"
done

# Add bootstrap file
echo "Adding bootstrap file..."
add_file "$BASE_DIR/bootstrap.php"

# Add other relevant files
echo "Adding other relevant files..."
add_file "$BASE_DIR/LocalTests/LocalTestRunNotifier.php"

# Add summary at the end
echo "" >> "$OUTPUT_FILE"
echo "=================================================================================" >> "$OUTPUT_FILE"
echo "SUMMARY" >> "$OUTPUT_FILE"
echo "=================================================================================" >> "$OUTPUT_FILE"
echo "" >> "$OUTPUT_FILE"
echo "Files concatenated on: $(date)" >> "$OUTPUT_FILE"
echo "Total files processed:" >> "$OUTPUT_FILE"

# Count files
file_count=0
echo "Main Commands:" >> "$OUTPUT_FILE"
for cmd in "QITCommand.php" "DynamicCommand.php" "DynamicCommandCreator.php" "CreateRunCommands.php" "RunActivationTestCommand.php"; do
    if [ -f "$BASE_DIR/Commands/$cmd" ]; then
        echo "  - Commands/$cmd" >> "$OUTPUT_FILE"
        ((file_count++))
    fi
done

echo "" >> "$OUTPUT_FILE"
echo "Environment Commands:" >> "$OUTPUT_FILE"
if [ -f "$BASE_DIR/Commands/Environment/UpEnvironmentCommand.php" ]; then
    echo "  - Commands/Environment/UpEnvironmentCommand.php" >> "$OUTPUT_FILE"
    ((file_count++))
fi

echo "" >> "$OUTPUT_FILE"
echo "Custom Test Commands:" >> "$OUTPUT_FILE"
if [ -f "$BASE_DIR/Commands/CustomTests/RunE2ECommand.php" ]; then
    echo "  - Commands/CustomTests/RunE2ECommand.php" >> "$OUTPUT_FILE"
    ((file_count++))
fi

echo "" >> "$OUTPUT_FILE"
echo "PreCommand Namespace Files:" >> "$OUTPUT_FILE"
find "$BASE_DIR/PreCommand" -name "*.php" -type f | sort | while read -r file; do
    relative_path="${file#$BASE_DIR/}"
    echo "  - $relative_path" >> "$OUTPUT_FILE"
    ((file_count++))
done

echo "" >> "$OUTPUT_FILE"
echo "Other Files:" >> "$OUTPUT_FILE"
for other in "bootstrap.php" "LocalTests/LocalTestRunNotifier.php"; do
    if [ -f "$BASE_DIR/$other" ]; then
        echo "  - $other" >> "$OUTPUT_FILE"
        ((file_count++))
    fi
done

echo "" >> "$OUTPUT_FILE"
echo "Total files: $file_count" >> "$OUTPUT_FILE"

echo ""
echo "========================================"
echo "Concatenation complete!"
echo "Output file: $OUTPUT_FILE"
echo "Total size: $(wc -c < "$OUTPUT_FILE") bytes"
echo "Total lines: $(wc -l < "$OUTPUT_FILE") lines"
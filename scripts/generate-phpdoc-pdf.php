<?php

/**
 * PHPDoc PDF Generator
 *
 * Generates comprehensive PDF documentation from PHPDoc comments
 * across all PHP files in the Laravel project.
 *
 * @author Digital Nomad Website
 *
 * @version 1.0.0
 */

require_once __DIR__.'/../vendor/autoload.php';

class PHPDocPDFGenerator
{
    private FPDF $pdf;

    private array $documentation = [];

    private string $projectName;

    private string $version;

    private string $outputPath;

    public function __construct(string $projectName = 'Digital Nomad Website', string $version = '1.0.0')
    {
        $this->projectName = $projectName;
        $this->version = $version;
        $this->outputPath = __DIR__.'/../storage/app/public/phpdoc-documentation.pdf';

        // Initialize PDF with A4 size
        $this->pdf = new FPDF;
        $this->pdf->SetAutoPageBreak(true, 20);
    }

    /**
     * Generate the complete PDF documentation
     */
    public function generate(): void
    {
        echo "🚀 Starting PHPDoc PDF Generation...\n";

        // Parse all PHP files
        $this->parsePHPFiles();

        // Generate PDF
        $this->createPDF();

        echo "✅ PDF generated successfully: {$this->outputPath}\n";
    }

    /**
     * Parse all PHP files in the project
     */
    private function parsePHPFiles(): void
    {
        echo "📁 Parsing PHP files...\n";

        $directories = [
            'app/Models',
            'app/Http/Controllers',
            'app/Services',
            'app/Console/Commands',
            'app/Filament/Resources',
            'app/Filament/Pages',
        ];

        foreach ($directories as $dir) {
            $path = __DIR__.'/../'.$dir;
            if (is_dir($path)) {
                $this->parseDirectory($path, $dir);
            }
        }
    }

    /**
     * Parse a directory for PHP files
     */
    private function parseDirectory(string $path, string $relativePath): void
    {
        $files = glob($path.'/*.php');

        foreach ($files as $file) {
            $relativeFile = $relativePath.'/'.basename($file);
            $this->parseFile($file, $relativeFile);
        }
    }

    /**
     * Parse a single PHP file for PHPDoc comments
     */
    private function parseFile(string $filePath, string $relativePath): void
    {
        $content = file_get_contents($filePath);
        if (! $content) {
            return;
        }

        $className = $this->extractClassName($content);
        if (! $className) {
            return;
        }

        $docData = [
            'file' => $relativePath,
            'class' => $className,
            'description' => $this->extractClassDescription($content),
            'properties' => $this->extractProperties($content),
            'methods' => $this->extractMethods($content),
            'category' => $this->categorizeFile($relativePath),
        ];

        $this->documentation[] = $docData;
        echo "  ✓ Parsed: {$className}\n";
    }

    /**
     * Extract class name from PHP content
     */
    private function extractClassName(string $content): ?string
    {
        if (preg_match('/class\s+(\w+)/', $content, $matches)) {
            return $matches[1];
        }

        return null;
    }

    /**
     * Extract class description from PHPDoc
     */
    private function extractClassDescription(string $content): string
    {
        if (preg_match('/\/\*\*\s*\n\s*\*\s*(.+?)\n/', $content, $matches)) {
            return trim($matches[1], ' *');
        }

        return 'No description available';
    }

    /**
     * Extract properties from PHPDoc
     */
    private function extractProperties(string $content): array
    {
        $properties = [];

        // Extract @property annotations
        if (preg_match_all('/@property\s+([^\s]+)\s+\$(\w+)(?:\s+(.+))?/', $content, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $match) {
                $properties[] = [
                    'type' => $match[1],
                    'name' => $match[2],
                    'description' => $match[3] ?? 'No description',
                ];
            }
        }

        return $properties;
    }

    /**
     * Extract methods from PHPDoc
     */
    private function extractMethods(string $content): array
    {
        $methods = [];

        // Extract method documentation
        if (preg_match_all('/\/\*\*\s*\n(.*?)\*\/\s*\n\s*(?:public|private|protected)?\s*(?:static\s+)?function\s+(\w+)\s*\([^)]*\)/', $content, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $match) {
                $docBlock = $match[1];
                $methodName = $match[2];

                $description = $this->extractMethodDescription($docBlock);
                $params = $this->extractMethodParams($docBlock);
                $return = $this->extractMethodReturn($docBlock);

                $methods[] = [
                    'name' => $methodName,
                    'description' => $description,
                    'params' => $params,
                    'return' => $return,
                ];
            }
        }

        return $methods;
    }

    /**
     * Extract method description from doc block
     */
    private function extractMethodDescription(string $docBlock): string
    {
        if (preg_match('/\*\s*([^*@]+)/', $docBlock, $matches)) {
            return trim($matches[1]);
        }

        return 'No description';
    }

    /**
     * Extract method parameters from doc block
     */
    private function extractMethodParams(string $docBlock): array
    {
        $params = [];

        if (preg_match_all('/@param\s+([^\s]+)\s+\$(\w+)(?:\s+(.+))?/', $docBlock, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $match) {
                $params[] = [
                    'type' => $match[1],
                    'name' => $match[2],
                    'description' => $match[3] ?? 'No description',
                ];
            }
        }

        return $params;
    }

    /**
     * Extract method return type from doc block
     */
    private function extractMethodReturn(string $docBlock): string
    {
        if (preg_match('/@return\s+([^\s]+)(?:\s+(.+))?/', $docBlock, $matches)) {
            return $matches[1].($matches[2] ? ' - '.$matches[2] : '');
        }

        return 'void';
    }

    /**
     * Categorize file based on path
     */
    private function categorizeFile(string $path): string
    {
        if (str_contains($path, 'Models')) {
            return 'Models';
        }
        if (str_contains($path, 'Controllers')) {
            return 'Controllers';
        }
        if (str_contains($path, 'Services')) {
            return 'Services';
        }
        if (str_contains($path, 'Commands')) {
            return 'Console Commands';
        }
        if (str_contains($path, 'Resources')) {
            return 'Filament Resources';
        }
        if (str_contains($path, 'Pages')) {
            return 'Filament Pages';
        }

        return 'Other';
    }

    /**
     * Create the PDF document
     */
    private function createPDF(): void
    {
        echo "📄 Generating PDF...\n";

        // Create cover page
        $this->createCoverPage();

        // Create table of contents
        $this->createTableOfContents();

        // Group documentation by category
        $grouped = $this->groupByCategory();

        // Create content pages
        foreach ($grouped as $category => $items) {
            $this->createCategorySection($category, $items);
        }

        // Output PDF
        $this->pdf->Output('F', $this->outputPath);
    }

    /**
     * Create cover page
     */
    private function createCoverPage(): void
    {
        $this->pdf->AddPage();

        // Title
        $this->pdf->SetFont('Arial', 'B', 24);
        $this->pdf->Cell(0, 20, $this->projectName, 0, 1, 'C');

        $this->pdf->Ln(10);

        // Subtitle
        $this->pdf->SetFont('Arial', 'B', 18);
        $this->pdf->Cell(0, 15, 'PHPDoc Documentation', 0, 1, 'C');

        $this->pdf->Ln(20);

        // Version
        $this->pdf->SetFont('Arial', '', 14);
        $this->pdf->Cell(0, 10, "Version: {$this->version}", 0, 1, 'C');

        $this->pdf->Ln(30);

        // Generated date
        $this->pdf->SetFont('Arial', '', 12);
        $this->pdf->Cell(0, 10, 'Generated: '.date('Y-m-d H:i:s'), 0, 1, 'C');

        $this->pdf->Ln(50);

        // Statistics
        $this->pdf->SetFont('Arial', 'B', 14);
        $this->pdf->Cell(0, 10, 'Documentation Statistics', 0, 1, 'C');

        $this->pdf->SetFont('Arial', '', 12);
        $this->pdf->Cell(0, 8, 'Total Classes: '.count($this->documentation), 0, 1, 'C');

        $categories = array_count_values(array_column($this->documentation, 'category'));
        foreach ($categories as $category => $count) {
            $this->pdf->Cell(0, 6, "{$category}: {$count}", 0, 1, 'C');
        }
    }

    /**
     * Create table of contents
     */
    private function createTableOfContents(): void
    {
        $this->pdf->AddPage();

        $this->pdf->SetFont('Arial', 'B', 16);
        $this->pdf->Cell(0, 15, 'Table of Contents', 0, 1, 'C');

        $this->pdf->Ln(10);

        $grouped = $this->groupByCategory();
        $pageNumber = 3; // Start after cover and TOC

        foreach ($grouped as $category => $items) {
            $this->pdf->SetFont('Arial', 'B', 14);
            $this->pdf->Cell(0, 10, $category, 0, 1);

            foreach ($items as $item) {
                $this->pdf->SetFont('Arial', '', 10);
                $this->pdf->Cell(20, 6, '', 0, 0); // Indent
                $this->pdf->Cell(0, 6, $item['class'], 0, 1);
            }

            $this->pdf->Ln(5);
            $pageNumber += ceil(count($items) / 2) + 1; // Estimate pages
        }
    }

    /**
     * Group documentation by category
     */
    private function groupByCategory(): array
    {
        $grouped = [];

        foreach ($this->documentation as $item) {
            $grouped[$item['category']][] = $item;
        }

        // Sort categories
        ksort($grouped);

        return $grouped;
    }

    /**
     * Create category section
     */
    private function createCategorySection(string $category, array $items): void
    {
        $this->pdf->AddPage();

        // Category header
        $this->pdf->SetFont('Arial', 'B', 16);
        $this->pdf->Cell(0, 15, $category, 0, 1, 'C');

        $this->pdf->Ln(10);

        foreach ($items as $item) {
            $this->createClassDocumentation($item);
        }
    }

    /**
     * Create documentation for a single class
     */
    private function createClassDocumentation(array $item): void
    {
        // Check if we need a new page
        if ($this->pdf->GetY() > 250) {
            $this->pdf->AddPage();
        }

        // Class name
        $this->pdf->SetFont('Arial', 'B', 12);
        $this->pdf->SetTextColor(0, 0, 139); // Dark blue
        $this->pdf->Cell(0, 8, $item['class'], 0, 1);

        // File path
        $this->pdf->SetFont('Arial', '', 8);
        $this->pdf->SetTextColor(100, 100, 100);
        $this->pdf->Cell(0, 5, "File: {$item['file']}", 0, 1);

        // Description
        $this->pdf->SetFont('Arial', '', 10);
        $this->pdf->SetTextColor(0, 0, 0);
        $this->pdf->MultiCell(0, 5, $item['description']);

        $this->pdf->Ln(3);

        // Properties
        if (! empty($item['properties'])) {
            $this->pdf->SetFont('Arial', 'B', 10);
            $this->pdf->Cell(0, 6, 'Properties:', 0, 1);

            foreach ($item['properties'] as $property) {
                $this->pdf->SetFont('Arial', '', 9);
                $this->pdf->Cell(20, 5, '', 0, 0); // Indent
                $this->pdf->Cell(30, 5, $property['type'], 0, 0);
                $this->pdf->Cell(20, 5, '$'.$property['name'], 0, 0);
                $this->pdf->Cell(0, 5, $property['description'], 0, 1);
            }

            $this->pdf->Ln(3);
        }

        // Methods
        if (! empty($item['methods'])) {
            $this->pdf->SetFont('Arial', 'B', 10);
            $this->pdf->Cell(0, 6, 'Methods:', 0, 1);

            foreach ($item['methods'] as $method) {
                $this->pdf->SetFont('Arial', '', 9);
                $this->pdf->Cell(20, 5, '', 0, 0); // Indent
                $this->pdf->Cell(0, 5, $method['name'].'()', 0, 1);

                // Method description
                $this->pdf->Cell(30, 4, '', 0, 0); // More indent
                $this->pdf->MultiCell(0, 4, $method['description']);

                // Parameters
                if (! empty($method['params'])) {
                    $this->pdf->Cell(30, 4, '', 0, 0);
                    $this->pdf->SetFont('Arial', 'I', 8);
                    $this->pdf->Cell(0, 4, 'Parameters:', 0, 1);

                    foreach ($method['params'] as $param) {
                        $this->pdf->Cell(40, 3, '', 0, 0);
                        $this->pdf->Cell(20, 3, $param['type'], 0, 0);
                        $this->pdf->Cell(15, 3, '$'.$param['name'], 0, 0);
                        $this->pdf->Cell(0, 3, $param['description'], 0, 1);
                    }
                }

                // Return type
                if ($method['return'] !== 'void') {
                    $this->pdf->Cell(30, 4, '', 0, 0);
                    $this->pdf->SetFont('Arial', 'I', 8);
                    $this->pdf->Cell(0, 4, 'Returns: '.$method['return'], 0, 1);
                }

                $this->pdf->Ln(2);
            }
        }

        $this->pdf->Ln(5);

        // Separator line
        $this->pdf->SetDrawColor(200, 200, 200);
        $this->pdf->Line(10, $this->pdf->GetY(), 200, $this->pdf->GetY());
        $this->pdf->Ln(5);
    }
}

// Main execution
if (php_sapi_name() === 'cli') {
    $generator = new PHPDocPDFGenerator;
    $generator->generate();
} else {
    echo "This script must be run from the command line.\n";
    exit(1);
}

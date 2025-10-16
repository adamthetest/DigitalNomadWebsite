# PHPDoc PDF Generator

A comprehensive PHP script that generates professional PDF documentation from PHPDoc comments across your Laravel project.

## 🚀 Features

- **Complete Documentation Coverage**: Parses all PHP files in Models, Controllers, Services, Commands, and Filament Resources
- **Professional PDF Output**: Generates a well-formatted PDF with cover page, table of contents, and organized sections
- **Automatic Categorization**: Groups classes by type (Models, Controllers, etc.) for easy navigation
- **Rich Content Extraction**: Extracts class descriptions, properties, methods, parameters, and return types
- **Statistics Overview**: Provides project statistics and documentation metrics

## 📋 Requirements

- PHP 8.2+
- Laravel project with PHPDoc comments
- FPDF library (automatically installed via Composer)

## 🛠️ Installation

The PDF generator is already set up in your project. The required dependencies are installed via Composer:

```bash
composer install
```

## 📖 Usage

### Generate PDF Documentation

```bash
# Using Composer script (recommended)
composer phpdoc-pdf

# Or run directly
php scripts/generate-phpdoc-pdf.php
```

### Output Location

The generated PDF will be saved to:
```
storage/app/public/phpdoc-documentation.pdf
```

## 📄 PDF Structure

The generated PDF includes:

1. **Cover Page**
   - Project name and version
   - Generation timestamp
   - Documentation statistics

2. **Table of Contents**
   - Organized by category
   - Quick navigation to all classes

3. **Category Sections**
   - Models
   - Controllers
   - Services
   - Console Commands
   - Filament Resources
   - Filament Pages

4. **Class Documentation**
   - Class name and file path
   - Description from PHPDoc
   - Properties with types and descriptions
   - Methods with parameters and return types

## 🔧 Customization

### Modify Project Information

Edit the script to change project details:

```php
$generator = new PHPDocPDFGenerator(
    projectName: 'Your Project Name',
    version: '2.0.0'
);
```

### Add More Directories

To include additional directories, modify the `parsePHPFiles()` method:

```php
$directories = [
    'app/Models',
    'app/Http/Controllers',
    'app/Services',
    'app/Console/Commands',
    'app/Filament/Resources',
    'app/Filament/Pages',
    'app/YourCustomDirectory', // Add your directory here
];
```

### Customize PDF Styling

The PDF uses FPDF for generation. You can modify:
- Fonts and sizes
- Colors
- Layout and spacing
- Page structure

## 📊 Parsed Information

The script extracts the following from PHPDoc comments:

### Class Level
- Class name
- File path
- Description from `/**` comment block

### Properties
- `@property` annotations
- Type information
- Property descriptions

### Methods
- Method names
- Descriptions
- `@param` annotations with types and descriptions
- `@return` annotations with return types

## 🎯 Example Output

The generated PDF will contain entries like:

```
City Model
File: app/Models/City.php
Represents a city in the digital nomad platform with cost of living data, 
coworking spaces, and local information.

Properties:
    string $name - The name of the city
    string $slug - URL-friendly identifier
    int $country_id - Foreign key to countries table
    float $cost_of_living_index - Cost of living score

Methods:
    country()
        Description: Get the country that owns the city
        Parameters: None
        Returns: \Illuminate\Database\Eloquent\Relations\BelongsTo

    neighborhoods()
        Description: Get all neighborhoods for this city
        Parameters: None
        Returns: \Illuminate\Database\Eloquent\Relations\HasMany
```

## 🔍 Troubleshooting

### Common Issues

1. **"Class FPDF not found"**
   - Ensure FPDF is installed: `composer require --dev setasign/fpdf`

2. **Empty PDF or missing classes**
   - Check that PHP files contain proper PHPDoc comments
   - Verify file paths in the script match your project structure

3. **Permission errors**
   - Ensure the `storage/app/public/` directory is writable

### Debug Mode

Add debug output to see what's being parsed:

```php
// In parseFile() method, add:
echo "Parsing: {$relativePath}\n";
echo "Class: {$className}\n";
echo "Properties: " . count($docData['properties']) . "\n";
echo "Methods: " . count($docData['methods']) . "\n\n";
```

## 🚀 Integration with CI/CD

You can integrate PDF generation into your CI/CD pipeline:

```yaml
# .github/workflows/ci.yml
- name: Generate PHPDoc PDF
  run: composer phpdoc-pdf

- name: Upload PDF Artifact
  uses: actions/upload-artifact@v3
  with:
    name: phpdoc-documentation
    path: storage/app/public/phpdoc-documentation.pdf
```

## 📈 Benefits

- **Professional Documentation**: Generate enterprise-grade documentation
- **Easy Sharing**: PDF format is universally accessible
- **Version Control**: Track documentation changes over time
- **Team Onboarding**: Help new developers understand the codebase
- **Client Deliverables**: Provide professional documentation to clients
- **Compliance**: Meet documentation requirements for audits

## 🔄 Automation

Consider setting up automated PDF generation:

```bash
# Add to your deployment script
composer phpdoc-pdf

# Or create a scheduled task
php artisan schedule:call 'composer phpdoc-pdf'
```

## 📝 Notes

- The script automatically excludes vendor files and focuses on your application code
- PHPDoc comments should follow standard format for best results
- Large projects may generate large PDFs - consider pagination for very large codebases
- The PDF is regenerated each time - previous versions are overwritten

---

**Generated by PHPDoc PDF Generator v1.0.0**

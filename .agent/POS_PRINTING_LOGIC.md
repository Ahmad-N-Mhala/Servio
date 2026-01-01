# Receipt Printing Logic Update

## Problem
The previous "Test Print" function triggered a standard browser print of the entire page, which included sidebars, headers, and backgrounds, making it unsuitable for thermal POS printers.

## Solution
Implemented a dedicated printing mechanism that isolates the receipt content and formats it specifically for thermal paper.

## Changes Made
Updated `printPreview` function in `resources/js/Pages/Settings/ReceiptTemplate.vue`:

1.  **Isolated Printing**:
    - Creates a temporary hidden `<iframe>` to hold *only* the receipt content.
    - Prevents printing of the settings form, sidebar, or navigation.

2.  **Thermal Printer formatting**:
    - injects CSS `@media print { @page { margin: 0; } }` to strip browser headers/footers.
    - Sets the body width to match the selected paper size (`58mm` or `80mm`).
    - Removes UI-specific styles like card shadows and borders (`border: none`, `box-shadow: none`).
    - Adds `grayscale(100%)` filter to images to simulate thermal output.

3.  **Style Preservation**:
    - Copies all existing stylesheets (Tailwind CSS) to the iframe to ensure the design matches the preview exactly.

## How it works for the User
1.  User selects paper size (e.g., 80mm).
2.  Clicks "Test Print".
3.  Browser print dialog opens.
4.  The preview shows **only the receipt**, perfect formatted.
5.  User selects their thermal printer and prints.

## Technical Code
```javascript
const printPreview = () => {
    // ... get content ...
    // ... create hidden iframe ...
    
    // Inject thermal-specific CSS
    doc.write(`
        <style>
            @media print {
                @page { margin: 0; size: auto; }
                body { margin: 0; padding: 0; }
            }
            body { 
                width: ${width}; /* 58mm or 80mm */
                background: white;
            }
            /* ... clean up styles ... */
        </style>
    `);
    
    // ... print iframe ...
};
```

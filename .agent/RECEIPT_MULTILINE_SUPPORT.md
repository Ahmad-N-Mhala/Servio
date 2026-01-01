# Receipt Multi-Line Support

## Overview
Updated the receipt customization fields to support multiple lines of text, allowing for more detailed headers, footers, and contact information.

## Changes Made

### 1. **Input Fields** (`ReceiptTemplate.vue`)
Converted the following fields from single-line text inputs to multi-line textareas:
- **Custom Header Text**: Now accepts multiple lines (e.g., for Tax Registration Number, Welcome Message).
- **Contact Information**: Now accepts multiple lines (e.g., Phone, Email, Website on separate lines).
- *Footer Text already supported multiple lines.*

### 2. **Text Rendering** (`ReceiptPreview.vue`)
Added `whitespace-pre-wrap` utility class to:
- Header Text display
- Footer Text display
- Contact Info display

This ensures that when you press `Enter` to create a new line in the input, it actually shows up as a new line on the receipt preview and the final print.

## How to Use
1.  Go to **Receipt Template** settings.
2.  In "Header Section", type your text. Press **Enter** to add a new line.
3.  Check the "Live Preview" to see the lines appear exactly as typed.
4.  Save and Print!

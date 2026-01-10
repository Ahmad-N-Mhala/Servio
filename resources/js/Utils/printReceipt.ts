
/**
 * Helper file for printing receipts using the iframe "snapshot" method.
 * This ensures identical output to the Receipt Settings preview.
 */

export const printReceiptPreview = (
    containerId: string,
    paperWidth: string | '80' | '58'
) => {
    const content = document.getElementById(containerId);
    if (!content) {
        console.error(`Print Error: Element with ID '${containerId}' not found.`);
        return;
    }

    // Create a hidden iframe
    const iframe = document.createElement('iframe');
    iframe.style.position = 'absolute';
    iframe.style.width = '0px';
    iframe.style.height = '0px';
    iframe.style.border = 'none';
    iframe.setAttribute('id', 'receipt-print-iframe');

    document.body.appendChild(iframe);

    const doc = iframe.contentWindow?.document;
    if (!doc) {
        document.body.removeChild(iframe);
        return;
    }

    // Write content
    doc.open();
    doc.write('<html><head><title>Receipt Print</title>');

    // Copy all styles from the current page to the iframe
    // This includes Tailwind classes and global styles
    const styles = document.querySelectorAll('style, link[rel="stylesheet"]');
    styles.forEach(style => {
        doc.write(style.outerHTML);
    });

    // Add specific print styles to override potential page layout issues
    const width = paperWidth === '58' ? '58mm' : '80mm';
    doc.write(`
        <style>
            @media print {
                @page { margin: 0; size: auto; }
                body { margin: 0; padding: 0; }
            }
            body { 
                background: white !important; 
                width: ${width} !important; 
                max-width: ${width} !important;
                margin: 0 auto !important;
                padding-bottom: 5mm !important;
                overflow: hidden !important;
                color: black !important;
            }
            /* Override container styles for printing */
            #${containerId} {
                border: none !important;
                box-shadow: none !important;
                padding: 0 !important;
                width: 100% !important;
                margin: 0 !important;
            }
            /* Ensure the component keeps its padding but doesn't overflow */
            .receipt-preview {
                box-sizing: border-box !important;
            }
             img {
                -webkit-filter: grayscale(100%); 
                filter: grayscale(100%);
            }
        </style>
    `);

    doc.write('</head><body>');
    doc.write(content.outerHTML); // Snapshot the current DOM state of the component
    doc.write('</body></html>');
    doc.close();

    // Print after resources load
    iframe.contentWindow?.focus();
    setTimeout(() => {
        iframe.contentWindow?.print();

        // Remove iframe after printing
        // We leave it slightly longer to ensure the print dialog has captured the content
        setTimeout(() => {
            try {
                document.body.removeChild(iframe);
            } catch (e) {
                // ignore
            }
        }, 1000);
    }, 500); // Wait for styles/images to load
};

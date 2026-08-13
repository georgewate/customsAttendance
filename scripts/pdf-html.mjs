import { chromium } from 'playwright';
import { readFile } from 'node:fs/promises';
import { resolve } from 'node:path';

const [inputHtml, outputPdf, headerTitle = 'Smart Attendance'] = process.argv.slice(2);

if (!inputHtml || !outputPdf) {
    console.error('usage: node scripts/pdf-html.mjs <input.html> <output.pdf> [headerTitle]');
    process.exit(1);
}

const html = await readFile(resolve(inputHtml), 'utf8');

const browser = await chromium.launch();
try {
    const page = await browser.newPage();
    await page.setContent(html, { waitUntil: 'networkidle' });
    await page.pdf({
        path: resolve(outputPdf),
        format: 'A4',
        printBackground: true,
        margin: { top: '1.9cm', bottom: '1.9cm', left: '1.5cm', right: '1.5cm' },
        displayHeaderFooter: true,
        headerTemplate: `<div style="font-size:8px;color:#64748b;width:100%;padding:0 1.5cm;box-sizing:border-box;display:flex;justify-content:space-between;font-family:Arial,sans-serif;">
            <span>${headerTitle}</span><span>Smart Attendance</span></div>`,
        footerTemplate: `<div style="font-size:8px;color:#64748b;width:100%;padding:0 1.5cm;box-sizing:border-box;display:flex;justify-content:space-between;font-family:Arial,sans-serif;">
            <span>Generated ${new Date().toLocaleString()}</span>
            <span>Page <span class="pageNumber"></span> of <span class="totalPages"></span></span></div>`,
    });
} finally {
    await browser.close();
}

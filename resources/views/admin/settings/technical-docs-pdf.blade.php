<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Smart Attendance &mdash; Technical Documentation</title>
    <style>
        body { font-family: 'Segoe UI', Arial, Helvetica, sans-serif; color: #1e293b; margin: 0; }
        .cover { background: linear-gradient(135deg, #1e2b47 0%, #1e40af 100%); color: #ffffff; padding: 28px 26px; border-radius: 10px; margin-bottom: 22px; }
        .cover h1 { margin: 0 0 6px; font-size: 24px; }
        .cover p { margin: 0; font-size: 12px; color: #c7d2fe; }
        .doc-body { font-family: 'Segoe UI', Arial, Helvetica, sans-serif; color: #1e293b; line-height: 1.65; font-size: 13px; }
        .doc-body h2 { font-size: 16px; color: #0c2d5e; border-bottom: 2px solid #e2e8f0; padding-bottom: 4px; margin: 24px 0 10px; }
        .doc-body h3 { font-size: 14px; color: #1e40af; margin: 16px 0 6px; }
        .doc-body p { margin: 7px 0; }
        .doc-body ul, .doc-body ol { margin: 7px 0 7px 22px; }
        .doc-body li { margin: 3px 0; }
        .doc-body code { font-family: Consolas, 'Courier New', monospace; background: #f1f5f9; padding: 1px 5px; border-radius: 4px; font-size: 12px; color: #0f172a; }
        .doc-body table { width: 100%; border-collapse: collapse; margin: 10px 0; font-size: 12px; }
        .doc-body th, .doc-body td { border: 1px solid #cbd5e1; padding: 5px 8px; text-align: left; vertical-align: top; }
        .doc-body th { background: #1e2b47; color: #ffffff; font-weight: 600; }
        .doc-body .callout { background: #eff6ff; border-left: 4px solid #3b82f6; padding: 10px 14px; border-radius: 0 6px 6px 0; margin: 12px 0; }
        .doc-body .callout-warn { background: #fffbeb; border-left-color: #f59e0b; }
        .doc-body .toc { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 12px 16px; margin: 12px 0; }
        .doc-body .toc ol { margin-left: 18px; }
        .doc-body .keep-together { break-inside: avoid; }
    </style>
</head>
<body>
    <div class="cover">
        <h1>Smart Attendance &mdash; Technical Documentation</h1>
        <p>Architecture, schema and operating notes for developers and IT &middot; generated {{ $generatedAt }}</p>
    </div>

    @include('admin.settings.partials.technical-docs-content')
</body>
</html>

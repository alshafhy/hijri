<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <title>{{ $report_title }}</title>
    <style>
        @page { margin: 24px 28px; }
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 11px;
            color: #1a1a1a;
            direction: rtl;
            text-align: right;
            line-height: 1.45;
        }
        h1, h2, h3 { margin: 0 0 8px; color: #1f3864; }
        h1 { font-size: 18px; }
        h2 { font-size: 14px; border-bottom: 2px solid #F79C20; padding-bottom: 4px; margin-top: 18px; }
        h3 { font-size: 12px; color: #F79C20; margin-top: 12px; }
        .meta { color: #555; margin-bottom: 12px; }
        .badge-manual {
            display: inline-block;
            background: #fff3cd;
            color: #856404;
            padding: 2px 8px;
            border-radius: 3px;
            font-size: 10px;
        }
        table.report {
            width: 100%;
            border-collapse: collapse;
            margin: 8px 0 14px;
        }
        table.report th, table.report td {
            border: 1px solid #d0d5dd;
            padding: 6px 8px;
            vertical-align: top;
        }
        table.report th {
            background: #F79C20;
            color: #fff;
            font-weight: bold;
            text-align: center;
        }
        table.report td.label {
            background: #f5f5f5;
            font-weight: bold;
            width: 28%;
            color: #1f3864;
        }
        .final-box {
            border: 2px solid #F79C20;
            border-radius: 6px;
            padding: 10px 12px;
            margin: 12px 0;
            background: #fffaf3;
        }
        .final-box .amount { font-size: 16px; font-weight: bold; color: #1f3864; }
        .page-break { page-break-before: always; }
        .muted { color: #888; font-size: 10px; }
        .watermark {
            position: fixed;
            top: 40%;
            left: 10%;
            width: 80%;
            text-align: center;
            font-size: 72px;
            color: rgba(180, 180, 180, 0.28);
            transform: rotate(-35deg);
            z-index: -1000;
            font-weight: bold;
        }
        .section-note {
            background: #fcfcfc;
            border: 1px solid #e2e8f0;
            padding: 8px 10px;
            margin-bottom: 10px;
            color: #555;
            font-size: 10px;
        }
    </style>
</head>
<body>
@if (!empty($watermark))
    <div class="watermark">{{ $watermark }}</div>
@endif

@yield('content')
</body>
</html>

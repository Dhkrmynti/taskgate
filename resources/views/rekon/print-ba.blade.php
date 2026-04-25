<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BA Rekon Material - {{ $rekon->id }}</title>
    <style>
        body { font-family: 'Times New Roman', Times, serif; padding: 40px; color: #111; line-height: 1.4; }
        .header { text-align: center; border-bottom: 3px double #000; padding-bottom: 20px; margin-bottom: 30px; }
        .header h1 { margin: 0; font-size: 20px; text-transform: uppercase; letter-spacing: 1px; }
        .header p { margin: 5px 0 0; font-size: 14px; font-weight: bold; }
        
        .info-section { width: 100%; border-collapse: collapse; margin-bottom: 30px; }
        .info-section td { padding: 5px 0; font-size: 12px; vertical-align: top; }
        
        .boq-table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        .boq-table th, .boq-table td { border: 1px solid #000; padding: 8px; text-align: left; font-size: 10px; }
        .boq-table th { background-color: #f2f2f2; font-weight: bold; text-align: center; text-transform: uppercase; }
        .text-right { text-align: right !important; }
        .text-center { text-align: center !important; }
        
        .footer-note { margin-top: 30px; font-size: 12px; }
        
        .signature-section { margin-top: 50px; width: 100%; }
        .signature-box { text-align: center; width: 50%; }
        .signature-name { margin-top: 70px; font-weight: bold; text-decoration: underline; }
        
        .btn-print { position: fixed; top: 20px; right: 20px; padding: 10px 20px; background: #2563eb; color: #fff; border: none; border-radius: 8px; font-weight: bold; cursor: pointer; box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1); }
        @media print { .btn-print { display: none; } }
    </style>
</head>
<body>
    <button class="btn-print" onclick="window.print()">PRINT DOCUMENT</button>

    <div class="header">
        <h1>Berita Acara Rekonsiliasi Material (BARM)</h1>
        <p>NOMOR: {{ $rekon->rekon_number ?? 'BA/'.date('Ymd').'/'.$rekon->id }}</p>
    </div>

    <table class="info-section">
        <tr>
            <td width="15%">ID Rekon</td>
            <td width="35%">: <strong>{{ $rekon->id }}</strong></td>
            <td width="15%">Tanggal</td>
            <td width="35%">: {{ $rekon->created_at->format('d F Y') }}</td>
        </tr>
        <tr>
            <td>Dibuat Oleh</td>
            <td>: {{ $rekon->creator->name ?? 'System' }}</td>
            <td>Batch Terkonsolidasi</td>
            <td>: {{ $rekon->batches->pluck('id')->implode(', ') }}</td>
        </tr>
    </table>

    <p style="font-size: 12px;">Berdasarkan hasil pengolahan data dan verifikasi fisik gudang, berikut adalah ringkasan material yang terkonsolidasi dalam Berita Acara ini:</p>

    <table class="boq-table">
        <thead>
            <tr>
                <th width="5%">No</th>
                <th width="15%">Designator</th>
                <th width="35%">Description</th>
                <th width="10%">Vol Planning</th>
                <th width="10%">Vol Pemenuhan</th>
                <th width="25%">Total Price (IDR)</th>
            </tr>
        </thead>
        <tbody>
            @php $grandTotal = 0; @endphp
            @foreach($rekon->boqDetails as $index => $item)
                @php 
                    $rowTotal = $item->volume_pemenuhan * $item->price_planning;
                    $grandTotal += $rowTotal;
                @endphp
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td class="text-center">{{ $item->designator }}</td>
                    <td>{{ $item->description }}</td>
                    <td class="text-right">{{ number_format($item->volume_planning) }}</td>
                    <td class="text-right">{{ number_format($item->volume_pemenuhan) }}</td>
                    <td class="text-right">{{ number_format($rowTotal, 0, ',', '.') }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr style="background-color: #f2f2f2; font-weight: bold;">
                <td colspan="5" class="text-right">GRAND TOTAL KONSOLIDASI</td>
                <td class="text-right">Rp {{ number_format($grandTotal, 0, ',', '.') }}</td>
            </tr>
        </tfoot>
    </table>

    <div class="footer-note">
        <p>Dokumen ini merupakan hasil konsolidasi otomatis dari beberapa batch pekerjaan (TGIDSP) yang telah diverifikasi oleh tim Warehouse.</p>
    </div>

    <table class="signature-section">
        <tr>
            <td class="signature-box">
                <p>Dilaporkan Oleh,</p>
                <p><strong>Team Warehouse</strong></p>
                <div class="signature-name">( {{ $rekon->creator->name ?? '...........................' }} )</div>
            </td>
            <td class="signature-box">
                <p>Disetujui Oleh,</p>
                <p><strong>Manager Integrated Resource</strong></p>
                <div class="signature-name">( ........................................... )</div>
            </td>
        </tr>
    </table>
</body>
</html>

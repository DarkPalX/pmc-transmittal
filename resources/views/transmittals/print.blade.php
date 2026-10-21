@php
    $isIncoming = $transmittal->transmittal_type === 'Incoming';
    $copies = $isIncoming ? ["Receiver's Copy", "Sender's Copy"] : ["Sender's Copy", "Receiver's Copy"];
@endphp
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8"><title>{{ $transmittal->ref_no }} · Printable</title>
    <style>
        @page{size:A4 portrait;margin:10mm 12mm}*{box-sizing:border-box}body{font-family:Arial,Helvetica,sans-serif;color:#111;margin:0;font-size:10px}.toolbar{display:flex;justify-content:flex-end;margin-bottom:12px}.toolbar button{background:#17202a;color:#fff;border:0;border-radius:4px;padding:8px 16px;cursor:pointer}.copy{position:relative;min-height:257mm;padding-top:3mm}.copy+.copy{page-break-before:always}.copy-label{position:absolute;right:0;top:0;color:#5b6770;font-size:10px;font-weight:bold}.head{display:grid;grid-template-columns:1fr 1fr;border-bottom:2px solid #111;padding-bottom:8px;margin-bottom:8px}.ref{font-weight:bold;font-size:12px}.destination{font-weight:bold;font-size:11px;text-transform:uppercase;margin-top:8px}.date{text-align:right;font-weight:bold;font-size:12px}.meta{display:grid;grid-template-columns:1fr 1fr;border-collapse:collapse;margin-top:8px}.meta div{border:1px solid #111;min-height:22px;padding:4px 7px}.meta label{font-weight:bold;display:inline-block;margin-right:8px}.items{width:100%;border-collapse:collapse;table-layout:fixed}.items th,.items td{border:1px solid #111;padding:5px 6px;vertical-align:middle}.items th{font-size:9px;text-align:center;height:24px}.items td{height:25px}.items .no{width:7%;text-align:center}.items .qty{width:8%;text-align:center}.items .unit{width:10%;text-align:center}.items .desc{width:32%;font-weight:bold}.items .serial{width:20%;text-align:center}.items .remarks{width:23%}.items .blank{height:25px}.nothing td{text-align:center;font-weight:bold;height:25px}.delivery{width:100%;border-collapse:collapse;margin-top:0}.delivery td{border:1px solid #111;height:22px;padding:4px 7px}.delivery .label{font-weight:bold;width:23%}.delivery .value{width:27%}.signatures{display:grid;grid-template-columns:1fr 1fr;gap:28px;margin-top:32px}.signature{min-height:47px;border-top:1px solid #111;padding-top:5px}.signature strong{font-size:10px}.signature span{display:block;font-size:9px;margin-top:3px}.footer-copy{text-align:right;font-weight:bold;font-size:11px;margin-top:18px}.note{margin-top:10px;border:1px solid #111;padding:6px;min-height:28px}.note strong{display:block;margin-bottom:3px}.outgoing .items .remarks{width:23%}.outgoing .items td{height:28px}.outgoing .delivery{margin-top:0}.outgoing .signatures{margin-top:25px}@media print{.toolbar{display:none}.copy{min-height:0}}
        /* Two compact copies are designed to print together on one A4 sheet. */
        body{padding-top:5mm}
        .copy{min-height:132mm;padding-top:7mm;page-break-inside:avoid}
        .copy+.copy{page-break-before:avoid;border-top:1px dashed #555;margin-top:6mm;padding-top:12mm}
        .copy+.copy .copy-watermark{top:4mm}
        .copy-watermark{position:absolute;top:0;left:0;right:0;text-align:center;color:#7f8990;font-size:14px;font-weight:bold;letter-spacing:.28em;opacity:.28}
        .copy-label{top:1mm}
        .items th,.items td{padding:3px 5px}.items th{height:19px}.items td{height:19px}
        .delivery td{height:17px;padding:3px 5px}.signatures{gap:18px;margin-top:16px}.signature{min-height:34px;padding-top:3px}.note{margin-top:6px;padding:4px;min-height:20px}.footer-copy{margin-top:7px;font-size:9px}
        @media print{body{padding-top:5mm}.copy{min-height:132mm}.copy+.copy{page-break-before:avoid;padding-top:12mm}}
    </style>
</head>
<body>
<div class="toolbar"><button onclick="window.print()">Print two copies</button></div>
@foreach($copies as $copy)
<section class="copy {{ $isIncoming ? 'incoming' : 'outgoing' }}">
    <div class="copy-watermark">{{ strtoupper($transmittal->transmittal_type) }}</div>
    <div class="copy-label">{{ $copy }}</div>
    <header class="head"><div><div class="ref">Reference No.: {{ $transmittal->ref_no }}</div><div class="destination">{{ $isIncoming ? 'Source' : 'Destination' }}: {{ $transmittal->destination }}</div></div><div class="date">DATE: {{ $transmittal->transmittal_date->format('F d, Y') }}</div></header>
    <table class="items"><thead><tr><th class="no">ITEM NO.</th><th class="qty">QTY</th><th class="unit">UNIT</th><th class="desc">ITEM DESCRIPTION</th><th class="serial">SERIAL NUMBER</th><th class="remarks">REMARKS</th></tr></thead><tbody>@forelse($transmittal->details as $i=>$detail)<tr><td class="no">{{ $i+1 }}</td><td class="qty">{{ rtrim(rtrim(number_format((float)($detail->item_qty ?? 1), 2, '.', ''), '0'), '.') }}</td><td class="unit">{{ strtoupper($detail->item_unit ?: '—') }}</td><td class="desc">{{ strtoupper($detail->item_desc) }}</td><td class="serial">{{ strtoupper($detail->item_serial_no ?: 'N/A') }}</td><td class="remarks">{{ $detail->remarks ?: '—' }}</td></tr>@empty<tr><td colspan="6" class="blank"></td></tr>@endforelse<tr class="nothing"><td></td><td></td><td></td><td>******** NOTHING TO FOLLOW ********</td><td>**************</td><td>**************</td></tr></tbody></table>
    <table class="delivery"><tbody><tr><td class="label">Dispatched By:</td><td class="value">{{ $transmittal->prepared_by }}</td><td></td></tr><tr><td class="label">Delivered By:</td><td class="value">{{ $transmittal->received_by ?: ' ' }}</td><td></td></tr><tr><td class="label">Mode of Delivery:</td><td class="value">Vehicle Transport</td><td class="label">Plate Number: {{ $transmittal->plate_no ?: ' ' }}</td></tr></tbody></table>
    <div class="note"><strong>Remarks:</strong>{{ $transmittal->remarks ?: ' ' }}</div>
    <div class="signatures"><div class="signature"><strong>Prepared By: {{ $transmittal->prepared_by }}</strong><span>(Name and Signature)</span></div><div class="signature"><strong>Noted By: {{ $transmittal->noted_by ?: ' ' }}</strong><span>(Name and Signature)</span></div><div class="signature"><strong>Received By: {{ $transmittal->received_by ?: ' ' }}</strong><span>(Name and Signature)</span></div><div class="signature"><strong>{{ $isIncoming ? 'Receiver Confirmation' : 'Sender Confirmation' }}</strong><span>(Name and Signature)</span></div></div>
    <div class="footer-copy">{{ $copy }}</div>
</section>
@endforeach
</body>
</html>

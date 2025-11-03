@php
    $customerName = $purchase->user->full_name ?? $purchase->customer_details['name'] ?? 'Customer';
    $customerEmail = $purchase->user->email ?? $purchase->customer_details['email'] ?? '';
    $customerPhone = $purchase->user->phone ?? $purchase->customer_details['phone'] ?? null;
    $planName = $purchase->ticketLimitPricing->name ?? 'Additional Tickets';
    $planDescription = $purchase->ticketLimitPricing->description ?? 'Additional tickets for event';
    $tickets = $purchase->additional_tickets;
    $amount = $purchase->amount;
    $subtotal = $amount;
    $tax = 0;
    $grand = $amount;
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Invoice - Additional Tickets</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <script>
    (function() {
      try {
        var theme = localStorage.getItem('theme');
        if (theme === 'dark' || (!theme && window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
          document.documentElement.classList.add('dark');
        }
      } catch (e) {}
    })();
  </script>
  <style>
    body { background:#f8f9fa; font-family:'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; font-size:14px; }
    .invoice-box { background:#fff !important; padding:28px; border-radius:10px; box-shadow:0 0 10px rgba(0,0,0,.1); max-width:760px; margin:20px auto; color:#000 !important; }
    .invoice-header { border-bottom:2px solid #14b8a6; margin-bottom:20px; }
    .invoice-header h1 { font-size:28px; color:#14b8a6; }
    .table th, .table td { vertical-align:middle; }
    .total-row { font-weight:bold; }
    .no-print { }
    /* Dark mode overrides - Keep paper light */
    .dark body { background:#0b1220; color:#e5e7eb; }
    .dark .invoice-box { background:#fff !important; color:#000 !important; box-shadow:0 0 10px rgba(0,0,0,.4); }
    .dark .invoice-header { border-bottom-color:#14b8a6; }
    .dark .invoice-header h1 { color:#14b8a6; }
    .dark .table { color:#000 !important; }
    .dark .table-light { background-color:#f8f9fa !important; color:#000 !important; }
    .dark .table-bordered, .dark .table-bordered > :not(caption) > * { border-color:#dee2e6; }
    .dark .text-muted, .dark .table .text-muted { color:#6c757d !important; }
    .dark .alert-secondary { background-color:#f8f9fa !important; color:#000 !important; border-color:#dee2e6; }
    @page { 
      size: A4; 
      margin: 15mm; 
    }
    @media print { 
      * { 
        -webkit-print-color-adjust: exact !important; 
        color-adjust: exact !important; 
        print-color-adjust: exact !important; 
      }
      body { 
        background:#fff !important; 
        font-size:11px !important; 
        color:#000 !important; 
        margin:0 !important; 
        padding:0 !important; 
        line-height:1.3 !important;
      }
      .invoice-box { 
        box-shadow:none !important; 
        margin:0 !important; 
        padding:0 !important; 
        max-width:100% !important; 
        width:100% !important;
        background:#fff !important; 
        color:#000 !important; 
        border-radius:0 !important;
      }
      .invoice-header { 
        margin-bottom:15px !important; 
        padding-bottom:10px !important;
        border-bottom:2px solid #14b8a6 !important;
      }
      .invoice-header h1 { 
        font-size:20px !important; 
        color:#14b8a6 !important; 
        margin:0 !important;
      }
      .invoice-header p { 
        font-size:9px !important; 
        margin:0;
      }
      .table { 
        font-size:10px !important; 
        margin-bottom:12px !important;
      }
      .table th { 
        padding:6px 8px !important; 
        font-size:9px !important;
      }
      .table td { 
        padding:6px 8px !important; 
        font-size:10px !important;
      }
      .no-print { display:none !important; }
    }
  </style>
  <script>
    document.addEventListener('DOMContentLoaded', function(){
      const u=new URL(window.location.href);
      if(u.searchParams.get('print')==='1'){ window.print(); }
      const backBtn=document.getElementById('invoice-back-btn');
      if(backBtn){
        backBtn.addEventListener('click',function(e){
          e.preventDefault();
          const from=u.searchParams.get('from');
          if(from==='profile'){ window.location.href='{{ route('profile.edit') }}#purchase-history'; return; }
          if(document.referrer){ history.back(); } else { window.location.href='{{ route('organizer.dashboard') }}'; }
        });
      }
    });
  </script>
</head>
<body>

<!-- Toolbar (not printed) -->
<div class="container mt-3 no-print d-flex justify-content-between" style="max-width:900px;">
  <a href="#" id="invoice-back-btn" class="btn btn-outline-secondary btn-sm">&larr; Back</a>
  <button class="btn btn-primary btn-sm" onclick="window.print()">Print</button>
</div>

<div class="invoice-box">
  <div class="invoice-header d-flex justify-content-between align-items-center">
    <div>
      <h1>Invoice</h1>
      <p>Purchase ID: <strong>{{ $purchase->purchase_id }}</strong></p>
    </div>
    <div class="text-end">
      <h5 style="margin:0; font-size:18px;">Qricket</h5>
      <p style="margin:0; font-size:12px;">Event Ticket Platform<br>Philippines</p>
    </div>
  </div>

  <div class="row mb-4">
    <div class="col-md-6">
      <h6><strong>Bill To:</strong></h6>
      <p>{{ $customerName }}<br>
      {{ $customerEmail }}</p>
    </div>
    <div class="col-md-6 text-end">
      <h6><strong>Invoice Details</strong></h6>
      <p>Invoice #: {{ $purchase->purchase_id }}<br>
      Date: {{ $purchase->paid_at?->format('M d, Y') ?? $purchase->created_at->format('M d, Y') }}<br>
      Status: <span class="badge bg-success">{{ ucfirst($purchase->status) }}</span></p>
      @if($purchase->xendit_invoice_id)
        <p style="margin-top:5px; font-size:11px;">Xendit ID: {{ $purchase->xendit_invoice_id }}</p>
      @endif
    </div>
  </div>

  <h6><strong>Purchase Details</strong></h6>
  <p>{{ $planName }}<br>
  {{ $planDescription }}<br>
  Event: {{ $purchase->event?->title ?? 'N/A' }}<br>
  @if($purchase->event?->start_date)
    Event Date: {{ $purchase->event->start_date->format('M d, Y') }}
  @endif</p>

  <table class="table table-bordered mt-4">
    <thead class="table-light">
      <tr>
        <th>DESCRIPTION</th>
        <th class="text-end">QTY</th>
        <th class="text-end">UNIT PRICE</th>
        <th class="text-end">AMOUNT</th>
      </tr>
    </thead>
    <tbody>
      <tr>
        <td>{{ $planName }}</td>
        <td class="text-end">{{ $tickets }}</td>
        <td class="text-end">₱{{ number_format($amount / $tickets, 2, '.', ',') }}</td>
        <td class="text-end">₱{{ number_format($amount, 2, '.', ',') }}</td>
      </tr>
      <tr>
        <td colspan="3" class="text-end">Subtotal</td>
        <td class="text-end">₱{{ number_format($subtotal, 2, '.', ',') }}</td>
      </tr>
      <tr>
        <td colspan="3" class="text-end">Tax</td>
        <td class="text-end">₱{{ number_format($tax, 2, '.', ',') }}</td>
      </tr>
      <tr class="total-row">
        <td colspan="3" class="text-end">Total</td>
        <td class="text-end">₱{{ number_format($grand, 2, '.', ',') }}</td>
      </tr>
    </tbody>
  </table>

  <div class="mt-4">
    <p><strong>Payment Method:</strong> {{ ucfirst($purchase->payment_method ?? 'Unknown') }}<br>
    <strong>Total Paid:</strong> ₱{{ number_format($purchase->amount, 2, '.', ',') }}</p>
  </div>

  <div class="alert alert-secondary mt-4">
    <strong>Refund Policy:</strong> You may request a full refund within 7 days of your purchase. 
    After the 7-day period, all sales are final and non-refundable. 
    To request a refund, please contact our support team through your account dashboard.
  </div>

  <div class="alert alert-secondary mt-3">
    <strong>Purchase Terms:</strong> This purchase provides {{ $tickets }} additional tickets for the event. 
    Tickets are valid for the event date and cannot be transferred or refunded after the 7-day refund period.
  </div>

  <div class="text-center mt-5">
    <p class="text-muted small mb-0">© {{ date('Y') }} Qricket. All rights reserved.</p>
  </div>
</div>

</body>
</html>


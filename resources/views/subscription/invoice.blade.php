@php
    $customerName = $user->full_name ?? $user->name;
    $customerEmail = $user->email;
    $planName = $plan?->name ?? 'Subscription Plan';
    $planDescription = $plan?->description ?? 'Event organizer subscription';
    $subtotal = (float)$amount;
    $tax = 0; // No tax for now
    $grand = $subtotal + $tax;
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Invoice - {{ $planName }}</title>
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
        font-size:10px !important; 
        margin:5px 0 !important;
      }
      .row { 
        margin-bottom:10px !important; 
      }
      .col-md-6 h6 { 
        font-size:11px !important; 
        margin-bottom:5px !important;
      }
      .col-md-6 p { 
        font-size:10px !important; 
        margin-bottom:3px !important;
        line-height:1.2 !important;
      }
      .table { 
        font-size:10px !important; 
        margin-top:10px !important;
        margin-bottom:10px !important;
      }
      .table th, .table td { 
        padding:4px 6px !important; 
        color:#000 !important; 
        border:1px solid #dee2e6 !important;
        vertical-align:middle !important;
      }
      .table th { 
        background-color:#f8f9fa !important; 
        font-weight:bold !important;
        font-size:9px !important;
      }
      .table-light { 
        background-color:#f8f9fa !important; 
        color:#000 !important; 
      }
      .total-row { 
        font-weight:bold !important; 
        background-color:#f8f9fa !important;
      }
      .text-muted { 
        color:#6c757d !important; 
        font-size:9px !important;
      }
      .alert-secondary { 
        background-color:#f8f9fa !important; 
        color:#000 !important; 
        border:1px solid #dee2e6 !important;
        padding:8px !important;
        margin-top:10px !important;
        font-size:9px !important;
      }
      .badge { 
        font-size:8px !important; 
        padding:2px 4px !important;
      }
      .no-print { 
        display:none !important; 
      }
      h6 { 
        font-size:11px !important; 
        margin-bottom:5px !important;
      }
      p { 
        margin-bottom:3px !important; 
        line-height:1.2 !important;
      }
      h5 { 
        font-size:16px !important; 
        margin:0 !important;
      }
      .text-end h5 { 
        font-size:16px !important; 
      }
      .text-end p { 
        font-size:10px !important; 
        margin:0 !important;
      }
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
          if(document.referrer){ history.back(); } else { window.location.href='{{ route('subscription.current') }}'; }
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
      <p>Subscription ID: <strong>{{ $subscription->id }}</strong></p>
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
      <p>Invoice #: {{ $subscription->id }}<br>
      Date: {{ $invoice_date->format('M d, Y') }}<br>
      Status: <span class="badge bg-success">{{ ucfirst($subscription->status) }}</span></p>
      @if($subscription->xendit_invoice_id)
        <p style="margin-top:5px; font-size:11px;">Xendit ID: {{ $subscription->xendit_invoice_id }}</p>
      @endif
    </div>
  </div>

  <h6><strong>Subscription Details</strong></h6>
  <p>{{ $planName }}<br>
  {{ $planDescription }}<br>
  @if($due_date)
    Valid until: {{ $due_date->format('M d, Y') }}
  @else
    No expiration date
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
        <td class="text-end">1</td>
        <td class="text-end">₱{{ number_format($subtotal, 2, '.', ',') }}</td>
        <td class="text-end">₱{{ number_format($subtotal, 2, '.', ',') }}</td>
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
    <p><strong>Payment Method:</strong> {{ ucfirst($subscription->payment_method ?? 'Unknown') }}<br>
    <strong>Total Paid:</strong> ₱{{ number_format($subscription->amount, 2, '.', ',') }}</p>
  </div>

  <div class="alert alert-secondary mt-4">
    <strong>Refund Policy:</strong> You may request a full refund within 7 days of your purchase. 
    After the 7-day period, all sales are final and non-refundable. 
    To request a refund, please contact our support team through your account dashboard.
  </div>

  <div class="alert alert-secondary mt-3">
    <strong>Subscription Terms:</strong> This subscription provides access to organizer features and event management tools. 
    @if($due_date)
      Your subscription will automatically renew on {{ $due_date->format('M d, Y') }} unless cancelled.
    @else
      This is a lifetime subscription with no expiration date.
    @endif
  </div>

  <div class="text-center mt-5">
    <p class="text-muted small mb-0">© {{ date('Y') }} Qricket. All rights reserved.</p>
  </div>
</div>

</body>
</html>

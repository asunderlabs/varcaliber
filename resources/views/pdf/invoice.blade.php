<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>@yield('title')</title>

        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Nunito:ital,wght@0,400;0,700;0,900;1,400&display=swap" rel="stylesheet">

        <style>

            @page { margin: 0px; }

            body { 
              margin: 0px; 
              font-size: 12px;
              font-family: 'Nunito';
              line-height: 1;
            }

            h3 {
              font-size: 1rem; font-weight: 600;
            }

            #page {
              margin: 40px;
            }

            #page > table {
              width: 100%;
            }

            #page > table {
              margin-bottom: 15px;
            }

            #options {
              /* border: 1px solid #d0d0d0; */
              border-left: 0;
              border-right: 0;
              /* padding: 10px 0; */
            }

            #details {
              border-collapse: collapse;
              margin-bottom: 10px !important;
            }

            #details th {
              border-bottom: 1px solid #000;
            }

            #details tr:nth-child(odd) td {
              background: #F0F0F0;
            }

            #details th,
            #details td {
              padding: 10px;
            }

            #details td {
              border-bottom: 1px solid #e0e0e0;
            }

            .special {
              background: #E7E7E7;
              padding: 10px;
              border-radius: 10px;
              border: 1px dashed #000;
              text-align: center;
              font-size: 1.1rem;
            }



            /* #header {
              background: #ffcccc;
            }

            #info {
              background: #ccffcc;
            } */


        </style>


    </head>
    <body>
      <div id="page">
        <table id="header">
          <tbody>
            @if ($logoBase64)
            <tr>
              <td align="center" style="padding: 10px 0;">
                <img style="width: 120px;" src="data:image/png;base64,{{ $logoBase64 }}">
              </td>
            </tr>
            @endif
            <tr>
              <td align="center" style="border: 1px solid #a0a0a0; border-left: 0; border-right: 0; padding: 5px 0;">
                {{ $company['address'] }} • {{ $company['phone'] }} • {{ $company['email'] }} 
              </td>
            </tr>
          </tbody>
        </table>

        <table id="info">
          <tbody>
            <tr>
              <td valign="top">
                <span style="font-size: 2.5rem;">
                  <span style="font-weight: 900;">Invoice</span> 
                  <span style="color: #6a6a6a">#{{ str_pad($invoice->number, 4, '0', STR_PAD_LEFT) }}</span>
                </span><br>
                <span style="font-size: 1rem;">{{ (new Carbon\Carbon($invoice->issue_at))->setTimezone('America/Chicago')->format('F j, Y') }}</span>
              </td>
              <td style="width: 200px;">
                <table style="width: 100%;">
                  <tbody>
                    <tr>
                      <td align="right" style="font-weight: 600;">Amount Due</td>
                    </tr>
                    <tr>
                      <td align="right" style="background: #F0F0F0; padding: 10px; font-size: 1.3rem; border-radius: 5px;">
                        ${{ number_format($invoice->total/100, 2)}}
                      </td>
                    </tr>
                    <tr>
                      <td align="right" style="font-style: italic;">
                        Please pay by {{ (new Carbon\Carbon($invoice->due_at))->setTimezone('America/Chicago')->format('M j, Y') }}
                      </td>
                    </tr>
                  </tbody>
                </table>
              </td>
            </tr>
            <tr>
          </tbody>
        </table>

        @if ($invoice->pay_by_bank_discount)
        <div class="special"><b>Save ${{ floor($invoice->pay_by_bank_discount/100) }}</b> when you pay online using your bank account</div>
        @endif

        <table id="options">
          <tbody>
            <tr>
              <td valign="top" style="width: 200px;">
                <h3>Bill To</h3>
                {{ $invoice->organization->billing_contact }}<br>
                {{ $invoice->organization->name }} <br>
                {{ $invoice->organization->address_line_1 }} {{ $invoice->organization->address_line_2 }}<br>
                {{ $invoice->organization->city }} {{ $invoice->organization->state }} {{ $invoice->organization->zip_code }}
              </td>
              <td valign="top">
                <h3>Payment Options</h3>
                <ol>
                  <li>Pay online using your debit or credit card.</li>
                  <li>Pay online using your bank account.</li>
                  <li>Mail a check. Make checks payable to “{{ $company['name'] }}" and send to the address above.</li>
                </ol>
              </td>
              @if ($url)
              <td align="right" style="width: 80px; padding-left: 50px;">
                <a href="{{ $url }}" style="display: inline-block; width: 100%; text-align: center; margin-bottom: 10px;">Pay Online</a>
                @if (!empty($payOnlineQRCodeBase64))
                <div style="display: block; border: 1px solid #6a6a6a; border-radius: 10px; padding: 10px; text-align: center; " >
                  <img width="60" height="60" style="display: block;" src="data:image/png;base64,{{$payOnlineQRCodeBase64}}">
                </div>
                @endif
              </td>
              @endif
            </tr>
          </tbody>
        </table>

        <table id="details">
            <thead>
                <tr>
                    <th align="left">Description</th>
                    <th align="left" style="width: 60px;">Qty</th>
                    <th align="left" style="width: 60px;">Rate</th>
                    <th align="right" style="width: 60px;">Amount</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($invoice->items as $item)
                <tr>
                    <td>{{ $item['description'] }}</td>
                    <td style="vertical-align: top;">{{ number_format($item['quantity'] ?? $item['hours'], 2) }}</td>
                    <td style="vertical-align: top;">{{ number_format($item['hourly_rate'], 2) }}</td>
                    <td style="vertical-align: top;" align="right">${{ number_format($item['amount']/100, 2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <table id="totals">
            <tbody>
                <tr>
                    <td style="width: 70%"></td>
                    <td>Subtotal: </td>
                    <td align="right">${{ number_format($invoice->subtotal/100, 2) }}</td>
                </tr>
                <tr>
                    <td style="width: 70%"></td>
                    <td>Tax: </td>
                    <td align="right">${{ number_format($invoice->tax/100, 2) }}</td>
                </tr>
                <tr>
                    <td style="width: 70%"></td>
                    <td>Total: </td>
                    <td align="right">${{ number_format($invoice->total/100, 2) }}</td>
                </tr>
                <tr>
                    <td style="width: 70%"></td>
                    <td><b>Amount Due:</b></td>
                    <td align="right"><b>${{ number_format($invoice->total/100, 2) }}</b></td>
                </tr>
            </tbody>
        </table>

        @if ($invoice->note)
        <table>
          <tbody>
            <tr>
              <td>
                <strong>Notes</strong>: <br>
                {{ $invoice->note }}
              </td>
            </tr>
          </tbody>
        </table>
        @endif
      </div>
    </body>
</html>

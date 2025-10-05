<div style="padding: 0.5rem;">
    <table style="width: 100%">
        <tbody class="strong">
            <tr class="gry-color" style="background: #F7F7F7;">
                <th width="80%"  style="padding: 1.5rem; text-align:left; font-weight:400">Subtotal</th>
                <td width="20%" style="text-align: center;">{{ $order->currency . $order->total }}</td>
            </tr>
            <tr class="gry-color" style="background: #F7F7F7;">
                <th style="padding: 1.5rem; text-align:left; font-weight:400;">Shipping</th>
                <td style="text-align: center;">{{ $order->currency . $order->shipping }}</td>
            </tr>
            <tr class="gry-color" style="background: #F7F7F7;">
                <th style="padding: 1.5rem; text-align:left;">Total</th>
                <th style="text-align: center;">{{ $order->currency . ($order->total + $order->shipping) }}</th>
            </tr>
        </tbody>
    </table>
</div>

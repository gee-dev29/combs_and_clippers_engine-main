@foreach ($order->orderItems as $item)
    <div style="height: 136px; width: 100%; display: flex; margin-bottom: 10px;">
        <!-- product image link here -->
        <div style="height: 100%; width: 100px;">
            <img src="{{ asset($item->image) }}" alt="product_img" style="height: 100%; width: 100px">
        </div>
        <!-- product name, quantity and price -->
        <div style="padding: 5px; display: flex; flex-direction: column; width: 85%; height: 100%;">
            <!-- product name here -->
            <div style="height: 40%;">
                <p style="font-size: 18px; font-weight: 400;">{{ $item->productname }}</p>
            </div>
            <div style="width: 100%; height: 50%; display: flex;">
                <div>
                    <p style="font-size: 14px; font-weight: 700;">Qty</p>
                    <!-- quantity value here -->
                    <p style="font-size: 18px; font-weight: 400;">x {{ $item->quantity }}</p>
                </div>
                <div style="margin-left: auto; display: flex;">
                    <!-- price value here -->
                    <span
                        style="font-size: 18px; font-weight: 700; margin-top: auto;">{{ $item->productInfo->currency . $item->price }}</span>
                </div>
            </div>
        </div>
    </div>
@endforeach

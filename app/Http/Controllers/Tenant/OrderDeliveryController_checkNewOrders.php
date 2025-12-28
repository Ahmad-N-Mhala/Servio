/**
* Check for new ready orders (JSON API for polling).
*/
public function checkNewOrders(Request $request)
{
// Permission check manually passed or middleware
if (!$request->user()->can('deliver_orders')) {
return response()->json(['ids' => []]);
}

$restaurant = $request->user()->currentRestaurant();
if (!$restaurant) {
return response()->json(['ids' => []]);
}

$ids = Order::where('restaurant_id', $restaurant->id)
->where('status', 'ready')
->pluck('id');

return response()->json(['ids' => $ids]);
}
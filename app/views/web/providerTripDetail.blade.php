<td colspan="4">
	<div class="row">
		<div id="trip-map" class="col-lg-4">
		<img width="250" height="250" src="{{ $map_url }}">
		</div>
		<div id="trip-info" class="col-lg-4">
			<div class="col-lg-12">
				<h2>Rs. {{ $request->total - $request->ledger_payment}}</h2>
				<p>{{ date('l, F d Y h:i A',strtotime($request->request_start_time)) }}</p>
			</div>
			<div class="col-lg-12">
			<br>
			</div>
			<div class="col-lg-12">
				<span class="glyphicon glyphicon-record" style="color:green" aria-hidden="true"></span>
				<span>{{ date('h:i A',strtotime($start->created_at)) }}</span>
				<div>
					{{ $start_address }}
				</div>
			</div>
			<div class="col-lg-12">
			<br>
			</div>
			<div class="col-lg-12">
				<span class="glyphicon glyphicon-record" style="color:red" aria-hidden="true"></span>
				<span>{{ date('h:i A',strtotime($end->created_at)) }}</span>
				<div>
					{{ $end_address }}
				</div>
			</div>

				
		</div>
		<div id="trip-action" class="col-lg-4">
		
			<div class="col-lg-12">
				<div class="col-lg-2">
				</div>
				<div class="col-lg-2">
					<p><a href="profile.html"><img src="{{ $owner->picture }}" class="img-circle" width="50"></a></p>
				</div>
				<div class="col-lg-8">
					<div class="col-lg-12">
						<b>{{ $owner->first_name }} {{ $owner->last_name }}</b>
					</div>
					<div class="col-lg-12">
					@for ($i = 1; $i <= $rating; $i++)
      				<span><img src="{{ web_url() }}/web/star.png"></span>
      				@endfor

					</div>
				</div>

			</div>
		
			<div class="col-lg-12" style="top:5px;">
				<center>
					<b>FARE BREAKDOWN</b>
					<table id="fare-table" style="position:relative;top:15px;">
					<tr>
						<td align="left">Base Fare</td>
						<td align="right">{{ $request->base_price }}</td>
					</tr>
					<tr>
						<td>Distance</td>
						<td align="right">{{ $request->distance_cost }}</td>
					</tr>
					<tr style="border-bottom: #cccccc solid 1px">
						<td>Time</td>
						<td align="right">{{ $request->time_cost }}</td>
					</tr>
					<tr>
						<td>Cost</td>
						<td align="right">{{ $request->total }}</td>
					</tr>
					<tr style="border-bottom: #cccccc solid 1px">
						<td>Promotion</td>
						<td align="right">-{{ $request->ledger_payment }}</td>
					</tr>
					<tr>
						<td><b>Charged</b></td>
						<td align="right"><b>{{ $request->card_payment }}</b></td>
					</tr>
				</table>
				</center>
			</div>
			<div class="col-lg-12">
				
			</div>

		</div>
</td>
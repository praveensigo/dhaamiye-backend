<!doctype html>
<html lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Dhaamiye: Earning Invoice</title>
</head>
<body>


    <style>
        /* reset */

        /***/
        /*{*/
        /*border: 0;*/
        /*box-sizing: content-box;*/
        /*color: inherit;*/
        /*font-family: inherit;*/
        /*font-size: inherit;*/
        /*font-style: inherit;*/
        /*font-weight: inherit;*/
        /*line-height: inherit;*/
        /*list-style: none;*/
        /*margin: 0;*/
        /*padding: 0;*/
        /*text-decoration: none;*/
        /*vertical-align: top;*/
        /*}*/

        /* content editable */

        *[contenteditable] { border-radius: 0.25em; min-width: 1em; outline: 0; }

        *[contenteditable] { cursor: pointer; }

        *[contenteditable]:hover, *[contenteditable]:focus, td:hover *[contenteditable], td:focus *[contenteditable], img.hover { background: #DEF; box-shadow: 0 0 1em 0.5em #DEF; }

        span[contenteditable] { display: inline-block; }

        /* heading */

        h1 { font: bold 100% sans-serif; letter-spacing: 0.5em; text-align: center; text-transform: uppercase; }

        /* table */

        table { font-size: 75%; table-layout: fixed; width: 100%; }
        table { border-collapse: collapse;  border:1px solid black; }
        th, td { border-width: 1px; padding: 0.5em; position: relative; text-align: left; }
        th, td { border-radius: 0.25em; border-style: solid; }
        th { background: #EEE; border-color: #BBB; }
        td { border-color: #DDD; }

        /* page */

        /*html { font: 16px/1 'Open Sans', sans-serif; overflow: auto; padding: 0.5in; }*/
        /*html { background: #999; cursor: default; }*/

        /*body { box-sizing: border-box; height: 11in; margin: 0 auto; overflow: hidden; padding: 0.5in; width: 8.5in; }*/
        /*body { background: #FFF; border-radius: 1px; box-shadow: 0 0 1in -0.25in rgba(0, 0, 0, 0.5); }*/

        /* header */

        header { margin: 0 0 3em; }
        header:after { clear: both; content: ""; display: table; }

        header h1 { background: #000; border-radius: 0.25em; color: #FFF; margin: 0 0 1em; padding: 0.5em 0; }
        header address { float: left; font-size: 75%; font-style: normal; line-height: 1.25; margin: 0 1em 1em 0; }
        header address p { margin: 0 0 0.25em; }
        header span, header img { display: block; float: right; }
        header span { margin: 0 0 1em 1em; max-height: 25%; max-width: 60%; position: relative; }
        header img { max-height: 100%; max-width: 100%; }
        header input { cursor: pointer; -ms-filter:"progid:DXImageTransform.Microsoft.Alpha(Opacity=0)"; height: 100%; left: 0; opacity: 0; position: absolute; top: 0; width: 100%; }

        /* article */

        article, article address, table.meta, table.inventory { margin: 0 0 3em; }
        article:after { clear: both; content: ""; display: table; }
        article h1 { clip: rect(0 0 0 0); position: absolute; }

        article address { float: left; font-size: 125%; font-weight: bold; }

        /* table meta & balance */

        table.meta, table.balance { float: right; width: 36%; }
        table.meta:after, table.balance:after { clear: both; content: ""; display: table; }

        /* table meta */

        table.meta th { width: 40%; }
        table.meta td { width: 60%; }

        /* table items */

        table.inventory { clear: both; width: 100%; }
        table.inventory th { font-weight: bold; text-align: center; }

        table.inventory td:nth-child(1) { width: 26%; }
        table.inventory td:nth-child(2) { width: 38%; }
        table.inventory td:nth-child(3) { text-align: right; width: 12%; }
        table.inventory td:nth-child(4) { text-align: right; width: 12%; }
        table.inventory td:nth-child(5) { text-align: right; width: 12%; }

        /* table balance */

        table.balance th, table.balance td { width: 50%; }
        table.balance td { text-align: right; }

        /* aside */

        aside h1 { border: none; border-width: 0 0 1px; margin: 0 0 1em; }
        aside h1 { border-color: #999; border-bottom-style: solid; }

        /* javascript */

        .add, .cut
        {
            border-width: 1px;
            display: block;
            font-size: .8rem;
            padding: 0.25em 0.5em;
            float: left;
            text-align: center;
            width: 0.6em;
        }

        .add, .cut
        {
            background: #9AF;
            box-shadow: 0 1px 2px rgba(0,0,0,0.2);
            background-image: -moz-linear-gradient(#00ADEE 5%, #0078A5 100%);
            background-image: -webkit-linear-gradient(#00ADEE 5%, #0078A5 100%);
            border-radius: 0.5em;
            border-color: #0076A3;
            color: #FFF;
            cursor: pointer;
            font-weight: bold;
            text-shadow: 0 -1px 2px rgba(0,0,0,0.333);
        }

        .add { margin: -2.5em 0 0; }

        .add:hover { background: #00ADEE; }

        .cut { opacity: 0; position: absolute; top: 0; left: -1.5em; }
        .cut { -webkit-transition: opacity 100ms ease-in; }

        tr:hover .cut { opacity: 1; }

    </style>


    <br/>
    <div class="row">
        <div class="col-md-12">
                <div class="panel-body">
                    <header>
                        <h1>EARNING REPORT</h1>
                        <h4>From Date : {{ date('d-m-Y',strtotime($from_date)) }}</h4>
                        <h4>To Date :   {{ date('d-m-Y',strtotime($to_date)) }}</h4>
                    </header>
	<body>		
		<div class="report-wrapper">		
			<table>					
				<tbody>
					<tr>
						<th>Sl. No.</th>
						<th>Order ID</th>
						<th>Delivery Date</th>
                        <th>Fuel Station</th>
						<th>Commission (Order Amount)</th>
						<th>Commission (Delivery Charge)</th>	
						<th>Total Commission</th>
                      						
							
					</tr>						
                    
                    @foreach($orders as $order)
						<tr>
							<td class="text-center" >{{ $order->sl_no }}</td>
							<td>{{ $order->order_id }}</td>
							<td>{{ date('d-m-Y',strtotime($order->date)) }}</td>
                            <td>{{ $order->fuel_station_name }}</td>
							<td>{{ $order->amount_commission }}</td>
							<td>{{ $order->delivery_charge_commission }}</td>
							<td>{{ $order->total_commission }}</td>

							<!-- <td></td>
							<td></td>
							<td></td> -->							
						</tr>		
					@endforeach
				</tbody>
			</table>			
		</div>

		
	</body>   
                       
                       
                       
                    
                    
                </div>
        </div>
    </div>
</body>
</html>
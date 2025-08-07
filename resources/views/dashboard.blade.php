@extends('template')

@section('content')
{{--    <h1>Bienvenido</h1>--}}
{{--    <p>Gestiona tus envíos masivos de WhatsApp de manera sencilla y eficiente.</p>--}}
{{--    <img--}}
{{--        src="https://cdn-icons-png.flaticon.com/512/124/124034.png"--}}
{{--        alt="WhatsApp"--}}
{{--        class="welcome-image"--}}
{{--    />--}}
    <div class="w-100 h-100">
        <div class="row">
            <div class="col-lg-6 col-md-6 col-sm-6">
                <div id="chart"></div>
            </div>
            <div class="col-lg-6 col-md-6 col-sm-6">

            </div>
            <div class="col-lg-6 col-md-6 col-sm-6">

            </div>
        </div>


    </div>
@endsection

@section('scripts')
    <script>
        var options = {
            chart: {
                type: 'bar'
            },
            series: [{
                name: 'sales',
                data: [30,40,45,50,49,60,70,91,125]
            }],
            xaxis: {
                categories: [1991,1992,1993,1994,1995,1996,1997, 1998,1999]
            }
        }

        var chart = new ApexCharts(document.querySelector("#chart"), options);

        chart.render();
    </script>
@endsection

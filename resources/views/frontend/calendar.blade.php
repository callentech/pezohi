<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

{{--    <meta name="current_user_email" content="{{ Auth::user()->email }}">--}}

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Scripts -->
    <!-- <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyAYNqIrc58KKRgaWvu7NLbx_k8BMpkDBcc&libraries=places" async></script> -->
    <script src="{{ asset('js/app.js') }}" defer></script>

    <!-- Fonts -->
    <link rel="dns-prefetch" href="//fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css?family=Nunito" rel="stylesheet">

    <!-- Styles -->
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
    <link href="{{ asset('css/style.min.css') }}" rel="stylesheet">

</head>
<body>
    <div id="app">

        <main class="frontend-main-content">

            <div id="top-nav" class="d-flex flex-column flex-md-row align-items-center p-3 px-md-4 mb-3 bg-white">
                <h5 class="my-0 mr-md-auto font-weight-normal">pezohi</h5>

                <a class="btn btn-outline-primary" href="#">Sign in</a>
                <a class="btn btn-primary" href="#">Sign up</a>
            </div>



            <frontend-calendar-data-component :data="{{ $calendar }}"></frontend-calendar-data-component>

{{--            <div class="header">--}}
{{--               <div class="container">--}}

{{--                   <div class="row p-3 px-md-4 mb-3">--}}

{{--                       <div class="col-6">--}}
{{--                           <div class="title">--}}
{{--                               <img src="{{ asset('/img/soccer_ball.png') }}" alt="">--}}
{{--                               <span class="text">Fall 2020 Westwood Boys 8th Grade Soccer</span>--}}
{{--                           </div>--}}
{{--                           <div class="description">--}}
{{--                               9 events . Owned by Brendan Levesque . Last updated 3 hours ago--}}
{{--                           </div>--}}
{{--                       </div>--}}

{{--                       <div class="col-md-6 text-right">--}}
{{--                           <div class="actions mt-2">--}}
{{--                               <button type="button" class="btn btn-primary"><i class="fas fa-bell"></i> Subscribe</button>--}}
{{--                               <button type="button" class="btn btn-primary"><i class="fas fa-user-plus"></i> Share</button>--}}
{{--                           </div>--}}
{{--                       </div>--}}

{{--                   </div>--}}
{{--               </div>--}}

{{--            </div>--}}

{{--            <div class="content">--}}
{{--                <div class="container">--}}
{{--                    <div class="row p-3 px-md-4 mb-3">--}}
{{--                        <table class="table table-bordered">--}}
{{--                            <thead>--}}
{{--                                <tr>--}}
{{--                                    <th scope="col">Date <i class="fas fa-sort-amount-down float-right"></i></th>--}}
{{--                                    <th scope="col">Time <i class="fas fa-sort-amount-down float-right"></i></th>--}}
{{--                                    <th scope="col">Address <i class="fas fa-sort-amount-down float-right"></i></th>--}}
{{--                                    <th scope="col">Event type <i class="fas fa-sort-amount-down float-right"></i></th>--}}
{{--                                    <th scope="col">Notes <i class="fas fa-sort-amount-down float-right"></i></th>--}}
{{--                                    <th scope="col">Status <i class="fas fa-sort-amount-down float-right"></i></th>--}}
{{--                                </tr>--}}
{{--                            </thead>--}}
{{--                            <tbody>--}}
{{--                                <tr>--}}
{{--                                    <td>10/3/2020</td>--}}
{{--                                    <td>4:30 PM - 5:30 PM</td>--}}
{{--                                    <td>255 Washington St. Westwood, MA</td>--}}
{{--                                    <td>Practice</td>--}}
{{--                                    <td>Arrive 5 minutes early</td>--}}
{{--                                    <td>Cancelled</td>--}}
{{--                                </tr>--}}
{{--                                <tr>--}}
{{--                                    <td>10/3/2020</td>--}}
{{--                                    <td>4:30 PM - 5:30 PM</td>--}}
{{--                                    <td>255 Washington St. Westwood, MA</td>--}}
{{--                                    <td>Practice</td>--}}
{{--                                    <td>Arrive 5 minutes early</td>--}}
{{--                                    <td>Cancelled</td>--}}
{{--                                </tr>--}}
{{--                                <tr>--}}
{{--                                    <td>10/3/2020</td>--}}
{{--                                    <td>4:30 PM - 5:30 PM</td>--}}
{{--                                    <td>255 Washington St. Westwood, MA</td>--}}
{{--                                    <td>Practice</td>--}}
{{--                                    <td>Arrive 5 minutes early</td>--}}
{{--                                    <td>Cancelled</td>--}}
{{--                                </tr>--}}
{{--                            </tbody>--}}
{{--                        </table>--}}
{{--                    </div>--}}
{{--                </div>--}}
{{--            </div>--}}

        </main>

    </div>
</body>
</html>

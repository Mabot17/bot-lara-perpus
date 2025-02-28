<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">
    <link rel="icon" href="favicon.ico">
    <title>Register BOT|POS</title>
    <!-- Simple bar CSS -->
    <link rel="stylesheet" href="{{ asset('css/simplebar.css') }}">
    <!-- Fonts CSS -->
    <link href="https://fonts.googleapis.com/css2?family=Overpass:ital,wght@0,100;0,200;0,300;0,400;0,600;0,700;0,800;0,900&display=swap" rel="stylesheet">
    <!-- Icons CSS -->
    <link rel="stylesheet" href="{{ asset('css/feather.css') }}">
    <!-- Date Range Picker CSS -->
    <link rel="stylesheet" href="{{ asset('css/daterangepicker.css')}}">
    <!-- App CSS -->
    <link rel="stylesheet" href="{{ asset('css/app-light.css') }}" id="lightTheme">
    <link rel="stylesheet" href="{{ asset('css/app-dark.css') }}" id="darkTheme" disabled>
    <style>
        .card-custom {
            width: 600px;
            max-width: 90%;
            margin: auto;
        }
    </style>
</head>
<body class="light">
    <div class="wrapper vh-100">
        <div class="row align-items-center h-100">
            <div class="card card-custom shadow">
                <div class="card-body">
                    <div class="text-center my-4">
                        <a class="navbar-brand mx-auto mt-2 flex-fill text-center" href="./index.html">
                            <svg version="1.1" id="logo" class="navbar-brand-img brand-md" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" viewBox="0 0 120 120" xml:space="preserve">
                                <g>
                                    <polygon class="st0" points="78,105 15,105 24,87 87,87" />
                                    <polygon class="st0" points="96,69 33,69 42,51 105,51" />
                                    <polygon class="st0" points="78,33 15,33 24,15 87,15" />
                                </g>
                            </svg>
                        </a>
                        <h2 class="my-3">Register</h2>
                    </div>
                    <form id="registerForm">
                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label for="username">Username</label>
                                <input type="text" id="username" class="form-control" required>
                            </div>
                            <div class="form-group col-md-6">
                                <label for="inputEmail4">Email</label>
                                <input type="email" class="form-control" id="inputEmail4" required>
                            </div>
                        </div>
                        <hr class="my-4">
                        <div class="form-group">
                            <label for="inputPassword5">New Password</label>
                            <input type="password" class="form-control" id="inputPassword5" required>
                        </div>
                        <div class="form-group">
                            <label for="inputPassword6">Confirm Password</label>
                            <input type="password" class="form-control" id="inputPassword6" required>
                        </div>
                        <button class="btn btn-lg btn-primary btn-block" type="submit">Sign up</button>
                        <p class="mt-5 mb-3 text-muted text-center">© 2024</p>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <script src="{{ asset('js/jquery.min.js')}}"></script>
    <script src="{{ asset('js/popper.min.js')}}"></script>
    <script src="{{ asset('js/moment.min.js')}}"></script>
    <script src="{{ asset('js/bootstrap.min.js')}}"></script>
    <script src="{{ asset('js/simplebar.min.js')}}"></script>
    <script src="{{ asset('js/daterangepicker.js')}}"></script>
    <script src="{{ asset('js/jquery.stickOnScroll.js')}}"></script>
    <script src="{{ asset('js/tinycolor-min.js')}}"></script>
    <script src="{{ asset('js/config.js')}}"></script>
    <script src="{{ asset('js/apps.js')}}"></script>
    <script>
        $(document).ready(function() {
            $('#registerForm').on('submit', function(e) {
                e.preventDefault();
                let username = $('#username').val();
                let email = $('#inputEmail4').val();
                let password = $('#inputPassword5').val();
                let confirmPassword = $('#inputPassword6').val();

                if (password !== confirmPassword) {
                    alert('Passwords do not match');
                    return;
                }

                $.ajax({
                    url: '/api/register',
                    method: 'POST',
                    contentType: 'application/json',
                    data: JSON.stringify({
                        username: username,
                        email: email,
                        password: password
                    }),
                    success: function(response) {
                        alert('Registrasi Berhasil');
                        window.location.href = '/login';
                    },
                    error: function(response) {
                        alert('Registrasi Gagal');
                    }
                });
            });
        });
    </script>
</body>
</html>

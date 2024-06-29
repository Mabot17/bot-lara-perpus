<!doctype html>
<html lang="en">
<script>
    window.onload = function() {
        const token = localStorage.getItem('token');
        if (token) {
            window.location.href = '/dashboard'; // Redirect to dashboard if token is found
        }
    };
</script>
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">
    <link rel="icon" href="favicon.ico">
    <title>Login BOT|POS</title>
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
      body {
        height: 100vh;
        display: flex;
        justify-content: center;
        align-items: center;
        background-color: #f8f9fa;
      }
      .card {
        width: 100%;
        max-width: 400px;
      }
      .alert {
        display: none;
      }
    </style>
  </head>
  <body class="light">
    <div class="card shadow">
        <div class="card-body">
            <form id="loginForm" class="text-center">
                <a class="navbar-brand mx-auto mt-2 flex-fill text-center" href="./index.html">
                    <svg version="1.1" id="logo" class="navbar-brand-img brand-md" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" viewBox="0 0 120 120" xml:space="preserve">
                        <g>
                            <polygon class="st0" points="78,105 15,105 24,87 87,87 	" />
                            <polygon class="st0" points="96,69 33,69 42,51 105,51 	" />
                            <polygon class="st0" points="78,33 15,33 24,15 87,15 	" />
                        </g>
                    </svg>
                </a>
                <h1 class="h6 mb-3">Sign in</h1>
                <div class="alert alert-danger alert-dismissible fade show" role="alert" id="errorMessage">
                    <span id="errorText"></span>
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="form-group">
                    <label for="inputEmail" class="sr-only">Username</label>
                    <input type="text" id="inputEmail" class="form-control form-control-lg" placeholder="Username" required autofocus>
                </div>
                <div class="form-group">
                    <label for="inputPassword" class="sr-only">Password</label>
                    <input type="password" id="inputPassword" class="form-control form-control-lg" placeholder="Password" required>
                </div>
                <div class="checkbox mb-3">
                    <label>
                    <input type="checkbox" value="remember-me"> Stay logged in </label>
                </div>
                <button class="btn btn-lg btn-primary btn-block" type="submit">Log in</button>
                <a class="btn btn-lg btn-primary btn-block" href="/register">Sign Up</a>
                <p class="mt-5 mb-3 text-muted">© 2024</p>
            </form>
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
    <!-- Global site tag (gtag.js) - Google Analytics -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=UA-56159088-1"></script>
    <script>
      window.dataLayer = window.dataLayer || [];

      function gtag() {
        dataLayer.push(arguments);
      }
      gtag('js', new Date());
      gtag('config', 'UA-56159088-1');

      $(document).ready(function() {

        $('#loginForm').on('submit', function(event) {
          event.preventDefault();
          const email = $('#inputEmail').val();
          const password = $('#inputPassword').val();

          $.ajax({
            url: '/api/login',
            method: 'POST',
            contentType: 'application/json',
            dataType: 'json',
            data: JSON.stringify({ username: email, password: password }),
            success: function(response) {
              if (response.status.code === 200) {
                const token = response.data.token;
                localStorage.setItem('token', token);
                window.location.href = '/dashboard'; // Arahkan ke halaman dashboard setelah login
              } else {
                $('#errorText').text(response.status.message);
                $('#errorMessage').show();
              }
            },
            error: function(xhr, status, error) {
              let errorMessage = 'An unknown error occurred';
              try {
                const response = JSON.parse(xhr.responseText);
                if (response.status && response.status.message) {
                  errorMessage = response.status.message;
                }
              } catch (e) {
                console.error('Error parsing error response:', e);
              }
              $('#errorText').text(errorMessage);
              $('#errorMessage').show();
            }
          });
        });
      });
    </script>
  </body>
</html>

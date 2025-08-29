<!doctype html>
<html lang="en">
  <head>
  	<title>House of Cars System Management</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
	   <!-- App favicon -->
        <link rel="shortcut icon" href="{{asset('dashboardv1/assets/images/houseofcars.png')}}">
	<link href="https://fonts.googleapis.com/css?family=Lato:300,400,700&display=swap" rel="stylesheet">

	<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
	
	<link rel="stylesheet" href="{{asset('loginv1/css/style.css')}}">

	</head>
	<body>
	<section class="ftco-section">
		<div class="container">
			
			<div class="row justify-content-center">
				<div class="col-md-6 col-lg-5">
					<div class="login-wrap p-4 p-md-5">
		      	<div class="icon d-flex align-items-center justify-content-center">
		      		<span class="fa fa-user-o"></span>
		      	</div>
		      	<h3 class="text-center mb-4">Login to Portal</h3>
                        <form method="POST" action="{{ route('login') }}" class="login-form">
                        @csrf

		      		<div class="form-group">
                          <input id="email" type="email" class="form-control @error('email') is-invalid @enderror rounded-left" placeholder="Email" name="email" value="{{ old('email') }}" required autocomplete="email" autofocus>

                            @error('email')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
		      		</div>
	            <div class="form-group d-flex">
                  <input id="password" type="password" class="form-control @error('password') is-invalid @enderror rounded-left" placeholder="Password" name="password" required autocomplete="current-password">

                    @error('password')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
	            </div>
	            <div class="form-group">
                    <button type="submit" class="btn btn-primary rounded submit p-3 px-5">
                                    {{ __('Login') }}
                                </button>

	            </div>
	          </form>
	        </div>
				</div>
			</div>
		</div>
	</section>

	<script src="{{asset('loginv1/js/jquery.min.js')}}"></script>
  <script src="{{asset('loginv1/js/popper.js')}}"></script>
  <script src="{{asset('loginv1/js/bootstrap.min.js')}}"></script>
  <script src="{{asset('loginv1/js/main.js')}}"></script>

	</body>
</html>


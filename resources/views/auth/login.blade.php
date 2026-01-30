<!DOCTYPE html>
<html lang="en">

<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

	<link rel="preconnect" href="https://fonts.gstatic.com">
	<link rel="shortcut icon" href="{{ asset('assets/img/icons/icon-48x48.png') }}" />

	<title>Login | {{ config('app.name') }}</title>

	<link href="{{ asset('assets/css/app.css') }}" rel="stylesheet">
	@include('includes.style')
	<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600&display=swap" rel="stylesheet">
	<style>
		  .user-icon {
            font-size: 60px;
            margin-bottom: 10px;
        }
	</style>
</head>

<body>
	<main class="d-flex w-100">
		<div class="container d-flex flex-column">
			<div class="row vh-100">
				<div class="col-sm-10 col-md-8 col-lg-6 col-xl-5 mx-auto d-table h-100">
					<div class="d-table-cell align-middle">
						<h3 class="mb-2 text-uppercase text-center fw-bold">{{ config('app.name') }}</h3>
						<div class="card">
							<div class="text-center mt-4">
								<i class="fa fa-user-circle user-icon text-primary"></i>
								<h5 class="mb-3 fw-bold">ADMIN</h5>
							</div>
							<div class="card-body">
								@if($errors->any())
								<div class="alert alert-danger p-1" role="alert">
									<ul class="mt-3">
										@foreach($errors->all() as $error)
											<li>{{ $error }}</li>
										@endforeach
									</ul>
								</div>
							@endif
								<div class="m-sm-3">
									<form id="formAuthentication" class="mb-3" action="{{ route('login') }}" method="POST">
										@csrf
										<div class="mb-3">
											<input class="form-control form-control-lg" type="text" name="username" placeholder="Username" />
										</div>
										<div class="mb-3">
											<input id="password" class="form-control form-control-lg" type="password" name="password" placeholder="Password" />
										</div>
										<div class="mb-3 form-check">
											<input type="checkbox" class="form-check-input" id="showPassword">
											<label class="form-check-label" for="showPassword">Tampilkan Password</label>
										</div>
										<div class="d-grid gap-2 mt-3">
											<button class="btn btn-primary d-grid w-100" type="submit">Masuk</button>
										</div>
									</form>
								</div>
							</div>
						</div>

					</div>
				</div>
			</div>
		</div>
	</main>

	<script src="{{ asset('assets/js/app.js') }}"></script>
	@include('includes.script')
	<script>
		document.getElementById('showPassword').addEventListener('change', function () {
			var passwordField = document.getElementById('password');
			if (this.checked) {
				passwordField.type = 'text';
			} else {
				passwordField.type = 'password';
			}
		});
	</script>

</body>

</html>


<!DOCTYPE html>
<html lang="en">

<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
	<title>@yield('title') | {{ config('app.name') }}</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
	@include('includes.style')
    @stack('style')
	@livewireStyles
</head>

<body>
	<div class="wrapper">
		@include('partials.sidebar')

		<div class="main">
			@include('partials.navbar')

			<main class="content">
				<div class="container-fluid p-0">

					<div class="row">
					    {{ $slot }}
					</div>
				</div>
			</main>
		</div>

	</div>
 
@include('includes.script')
@stack('script')
@livewireScripts

</body>

</html>
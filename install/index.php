<!doctype html>
<?php

if (!is_dir(__DIR__ . '/../vendor'))
{
	exit('Please run "composer install" first.');
}

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;

require __DIR__ . '/../vendor/autoload.php';

$app = new Application();
$app['env'] = getenv('APP_ENV') ?: 'production';

$app->register(new Igorw\Silex\ConfigServiceProvider(__DIR__ . '/../config/' . $app['env'] . '.json'));

$app->register(new Silex\Provider\DoctrineServiceProvider(), array(
    'db.options' => array(
        'driver'    => 'pdo_mysql',
        'dbname'   => $app['database']['name'],
        'host'      => $app['database']['host'],
        'user'      => $app['database']['user'],
        'password'  => $app['database']['password'],
    ),
));

if (isset($request))
{
	var_dump($request);
}

?>

<html>
<head>
	<title></title>
	<link rel="stylesheet" href="../vendor/css/bootstrap.css" />
	<link rel="stylesheet" href="../css/main.css" />

	<style>
	header {color: #fff;}
	header small {color: #dadada;}
	#main {padding-top: 10px; width: 1120px; overflow-x:hidden;}

	#sections {
		width: 5000px;
		overflow: hidden;
	}

	.section {
		vertical-align: top;
		display: inline-block;
		width: 1090px;
		margin-right: 40px;
	}
	</style>
	<script src="../vendor/js/jquery.js"></script>
	<script>
	$(function () {
		$(document.body).fadeIn(300);

		$('.next').on('click', function () {
			var margin = parseInt($('#sections').css('margin-left'), 10) - 1135
			
			$('#sections').animate({
				marginLeft: margin
			});
		});

		$('.prev').on('click', function () {
			var margin = parseInt($('#sections').css('margin-left'), 10) + 1135
			$('#sections').animate({
				marginLeft: margin
			});
		});

		$('.finish').on('click', function () {

			$('.next').trigger('click');

			$.post('', {
				name: 'Carl'
			}).done(function () {

			}).fail(function () {

			});
		});
	});
	</script>
</head>
<body>
	<header id="header">
		<div class="container">
			<h1>A Simple Forum <small>Installation</small></h1>
		</div>
	</header>

	<div id="main" class="container">
		<form method="post" action="javascript:void(0)" class="form-horizontal">
			<div id="sections">
				<div class="section">
					<section>
						<header>
							Database
						</header>
						<div class="content">
							<p>
								This section will install the database tables and populate them with starting data.
							</p>
							<p>
								To get started you will need to provide some information in order to install the database.
							</p>
							<br />
							
							<div class="form-group">
								<div class="col-sm-3">
									<label class="control-label">
										Database host
									</label>
								</div>
								<div class="col-sm-9">
									<input type="text" name="database-host" placeholder="127.0.0.1" class="form-control" />
								</div>
							</div>
							<div class="form-group">
								<div class="col-sm-3">
									<label class="control-label">
										Database user
									</label>
								</div>
								<div class="col-sm-9">
									<input type="text" name="database-user" placeholder="root" class="form-control" />
								</div>
							</div>
							<div class="form-group">
								<div class="col-sm-3">
									<label class="control-label">
										Database password
									</label>
								</div>
								<div class="col-sm-9">
									<input type="password" name="database-password" placeholder="" class="form-control" />
								</div>
							</div>
							<div class="form-group">
								<div class="col-sm-3">
									<label class="control-label">
										Database name
									</label>
								</div>
								<div class="col-sm-9">
									<input type="text" name="database-name" placeholder="" class="form-control" />
								</div>
							</div>

							<div class="form-group">
								<div class="col-sm-offset-3 col-sm-9">
									<button class="next btn btn-success">Next</button>
								</div>
							</div>
						</div>
					</section>

				</div>
				<div class="section">
					<section>
						<header>
							Admin user
						</header>
						<div class="content">
							<p>
								This will be the main user on the forum. It is used to access the administration section and change all aspects of the forum.
							</p>
							<br />
							<div class="form-group">
								<div class="col-sm-3">
									<label class="control-label">
										Username
									</label>
								</div>
								<div class="col-sm-9">
									<input type="text" name="username" placeholder="" class="form-control" />
								</div>
							</div>
							<div class="form-group">
								<div class="col-sm-3">
									<label class="control-label">
										Password
									</label>
								</div>
								<div class="col-sm-9">
									<input type="password" name="password" placeholder="" class="form-control" />
								</div>
							</div>
							<div class="form-group">
								<div class="col-sm-3">
									<label class="control-label">
										Confirm password
									</label>
								</div>
								<div class="col-sm-9">
									<input type="password" name="confirm" placeholder="" class="form-control" />
								</div>
							</div>

							<div class="form-group">
								<div class="col-sm-offset-3 col-sm-9">
									<button class="prev btn btn-success">Prev</button>
									<button class="finish btn btn-success">Finish</button>
								</div>
							</div>
						</div>
					</section>
				</div>
				<div class="section">
					<section>
						<header>
							Saving data
						</header>
						<div class="content">
							<p>
								A Simple Forum is writing your data to the database. Please wait.
							</p>
						</div>
					</section>
				</div>
			</div>

		</form>
	</div>

	<footer id="footer">

	</footer>
</body>
</html>
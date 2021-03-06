<!DOCTYPE HTML>
<html>
	<head>
		<title>after|mirror</title>
		<meta charset="utf-8" />
		<meta name="viewport" content="width=device-width, initial-scale=1" />
		<!--[if lte IE 8]><script src="assets/js/ie/html5shiv.js"></script><![endif]-->
		<link rel="stylesheet" href="assets/css/main.css" />
		<!--[if lte IE 8]><link rel="stylesheet" href="assets/css/ie8.css" /><![endif]-->
		<!--[if lte IE 9]><link rel="stylesheet" href="assets/css/ie9.css" /><![endif]-->
	</head>
	<body class="landing">

		<!-- Header -->
			<header id="header" class="alt">
				<h1><a href="/home.do">after|mirror</a></h1>
				<a href="#nav">menu</a>
			</header>

		<!-- Nav -->
			<nav id="nav">
				<ul class="links">
					<li><a href="/home.do">Home</a></li>
					<li><a href="https://latenight.moe">Watch</a></li>
					<li><a href="/music.do">Music</a></li>
					<li><a href="/gallery.do">Gallery</a></li>
					<li><a href="/library.do">Library</a></li>
					<li><a href="/cloud.do">Cloud</a></li>
					<?php
						if (defined("AUTH_USER")) {
							echo "
								<li><a href='/account.do'>" . AUTH_USER . "</a></li>
								<li><a href='/login.ps?logout'>Log Out</a></li>
							";
						}
						else {
							echo "
								<li><a href='/login.do'>Log In</a></li>
							";
						}
					?>
				</ul>
			</nav>

		<div class="minHeight">
		<!-- Banner -->
			<section id="banner" style="min-height: 100vh; position: relative;">
				<div style="position: absolute; top: 50%; left: 0; right: 0; margin-top: -5em; width: 100%;">
					<i class="icon fa-heart"></i>
					<h2>hey there.</h2>
					<p>welcome to after|mirror!</p>
				</div>
			</section>
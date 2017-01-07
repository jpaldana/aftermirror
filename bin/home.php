<?php
$page->show("header_banner");

$page->block("spanner", array("image" => false, "title" => "What do you want to do?", "content" => "Feel free to explore the entire site. Though, be warned that it is still in development and things are probably broken...", "href" => false, "text" => false));

$page->block("spanner-large", array("image" => "images/bg-anime.jpg", "title" => "watch stuff?", "content" => "Watch a select collection of anime and movies, with friends (if that's what you prefer!)", "href" => "/watch.do", "text" => "see listing"));
$page->block("spanner-large", array("image" => "images/bg-music.jpg", "title" => "listen to music?", "content" => "Did you want to listen to that wonderful soundtrack that brought tears to your eyes?", "href" => "/music.do", "text" => "open player"));
$page->block("spanner-large", array("image" => "images/bg-gallery.jpg", "title" => "look at the gallery?", "content" => "How about checking out some of the artwork collected throughout the various realms of the internet?", "href" => "/gallery.do", "text" => "view gallery"));
$page->block("spanner-large", array("image" => "images/bg-library.jpg", "title" => "read books?", "content" => "Perhaps a light novel or manga to pass the time?", "href" => "/library.do", "text" => "browse catalog"));
$page->block("spanner-large", array("image" => "images/bg-cloud.jpg", "title" => "upload files?", "content" => "Too lazy to use proper cloud storage?", "href" => "/cloud.do", "text" => "store files"));

$page->block("spanner", array("image" => false, "title" => "<code>about://after|mirror</code>", "content" => "So what exactly is after|mirror? I'm surprised if you randomly stumbled on this site without having someone else bring you here. after|mirror is more of a personal web development playground for the guy who pays for the server this website is hosted on. From the early prototypes in high school to what it is now, after|mirror continues to be a semi-actively developed side project for years to come. (maybe)", "href" => false, "text" => false));

$page->block("post", array("image" => false, "title" => "addendum", "content" => "<i class='fa fa-envelope'></i> Need to contact me? &mdash; <a href='mailto:admin@aftermirror.com'>Click here!</a><br/><i class='fa fa-github'></i> Want to view the source code? &mdash; <a href='https://github.com/jpaldana/aftermirror'>View on GitHub</a><br/><i class='fa fa-heartbeat'></i> Interested in the uptime stats? &mdash; <a href='/uptime.do'>View uptime report</a><br/><br/>Most importantly, <b>thanks to everyone who made this website possible</b>. &mdash; <a href='/credits.do'>Site Credits</a>", "href" => false, "text" => false, "article" => ""));

//$page->show("test");

$page->show("footer");
?>
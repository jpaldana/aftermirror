<?php
include("module/gallery.php");
$root = "data/gallery";
$gallery = new Gallery($mysqli);

if (isset($_GET["t"])) {
	$file = "data/gallery/thumbs/" . base64_decode($_GET["t"]);
	if (file_exists($file) && fextIsImage($file)) {
		$page->block("streamfile", array("file" => $file, "type" => "image/" . fext($file)));
	}
}
elseif (isset($_GET["i"])) {
	$file = "data/gallery/src/" . base64_decode($_GET["i"]);
	if (file_exists($file) && fextIsImage($file)) {
		$page->block("streamfile", array("file" => $file, "type" => "image/" . fext($file)));
	}
}
else {
	if ((isset($_GET["do"]) && $_GET["do"] == "iview")) {
		
	}
	else {
		$page->block("header", array("css" => array("https://cdnjs.cloudflare.com/ajax/libs/featherlight/1.7.0/featherlight.min.css", "/assets/css/gallery.css")));

		$page->block("spanner-largeoffset", array("image" => "images/bg-gallery.jpg", "title" => "look at the gallery?", "content" => "How about checking out some of the artwork collected throughout the various realms of the internet?", "href" => false, "text" => false));
		
		$page->block("spanner", array("image" => false, "title" => "gallery options", "content" => "<a class='button small' href='/gallery.do'>Home</a> <a class='button small' href='/gallery.do?do=new'>New</a>", "href" => false, "text" => false, "article" => ""));
	}

	$do = "default";
	if (isset($_GET["do"])) $do = $_GET["do"];

	echo "
	<section class='wrapper style1'>
	<div class='inner'>
	<article>
	";

	switch ($do) {
		case "new":
			requireLogin();
			if (isset($_POST["cName"])) {
				$gallery->createCollection(htmlentities($_POST["cName"]), $_POST["cDesc"], AUTH_UID);
				$id = max(array_keys($gallery->getCollections()));
				echo "
					<div class='callout callout-success'>
						<p>You have successfully created a new collection.</p>
						<p><a href='/gallery.do?do=view&gallery={$id}' class='btn btn-info content-link' data-title='Gallery'>Click here to continue</a></p>
					</div>
				";
			}
			echo "
				<div class='box box-primary'>
					<div class='box-header'>
						<h4 class='box-title'>New Collection</h4>
					</div>
					<div class='box-body'>
						<form action='/gallery.do?do=new' method='post' role='form' class='form'>
							<div class='form-group'>
								<label for='cName'>Collection Name</label>
								<input type='text' id='cName' name='cName' placeholder='(required)' class='form-control' />
							</div>
							<div class='form-group'>
								<label for='cDesc'>Collection Description</label>
								<textarea id='cDesc' name='cDesc' rows='6' class='form-control'></textarea>
							</div>
							<div class='form-group'>
								<input type='submit' value='Submit' class='form-control btn btn-primary' />
							</div>
						</form>
					</div>
				</div>
			";
		break;
		case "view":
			$info = $gallery->getCollectionAttributes($_GET["gallery"]);
			if (defined("AUTH_UID") && $info["creator"] == AUTH_UID) {
				if (!empty($_FILES) && file_exists($_FILES["file"]["tmp_name"])) {
					$tmp_name = $_FILES["file"]["tmp_name"];
					$file = AUTH_UID . sha1(uniqid() . AUTH_UID) . ".jpg";
					if (!is_dir("data/gallery/")) {
						mkdir("data/gallery/");
						mkdir("data/gallery/thumbs/");
						mkdir("data/gallery/src/");
					}
					$th_target = "data/gallery/thumbs/{$file}";
					$sr_target = "data/gallery/src/{$file}";
					iTF($tmp_name, $th_target, 500, 88);
					iTF($tmp_name, $sr_target, 2000, 91);
					$gallery->addMedia($_GET["gallery"], $file, "", "", AUTH_UID);
					echo "
					<div class='callout callout-success'>
						<p>You have uploaded a new file to your collection.</p>
					</div>
					";
				}
				elseif (isset($_POST["url"]) && (!filter_var($_POST["url"], FILTER_VALIDATE_URL) === false) && substr(strtolower($_POST["url"]), 0, 4) == "http") {
					$fgc = file_get_contents($_POST["url"]);
					$temp = sha1(uniqid() . AUTH_UID);
					$tmp_name = "data/gallery/src/_tmp_{$temp}.jpg";
					file_put_contents($tmp_name, $fgc);
					if (exif_imagetype($tmp_name)) {
						$file = AUTH_UID . sha1(uniqid() . AUTH_UID) . ".jpg";
						$th_target = "data/gallery/thumbs/{$file}";
						$sr_target = "data/gallery/src/{$file}";
						iTF($tmp_name, $th_target, 500, 88);
						iTF($tmp_name, $sr_target, 2000, 91);
						$gallery->addMedia($_GET["gallery"], $file, "", "");
						echo "
						<div class='callout callout-success'>
							<p>You have uploaded a new file to your collection.</p>
						</div>
						";
					}
					unlink($tmp_name);
				}
				elseif (isset($_GET["rem"])) {
					$gallery->delMedia($_GET["rem"]);
					echo "
					<div class='callout callout-success'>
						<p>You have successfully deleted a file.</p>
					</div>
					";
				}
				elseif (isset($_GET["rotate"])) {
					$infoRotate = $gallery->getMediaAttributes($_GET["rotate"]);
					$file = "data/gallery/src/{$infoRotate['source']}";
					$file_th = "data/gallery/thumbs/{$infoRotate['source']}";
					$source = imagecreatefromjpeg($file);
					$rotate = imagerotate($source, 90, 0);
					imagejpeg($rotate, $file, 100);
					imagedestroy($source);
					imagedestroy($rotate);
					$source = imagecreatefromjpeg($file_th);
					$rotate = imagerotate($source, 90, 0);
					imagejpeg($rotate, $file_th, 100);
					imagedestroy($source);
					imagedestroy($rotate);

					echo "
					<div class='callout callout-success'>
						<p>Rotation successful.</p>
					</div>
					";
				}
				echo "
					<a class='button small btn-app' id='gallery-upl-btn' href='#'><i class='fa fa-plus'></i> Add</a>
					<a class='button small btn-app' id='gallery-upl-btn-url' href='#'><i class='fa fa-plus'></i> Add (via URL)</a>
					<a class='button small btn-app content-link' href='/gallery.do?do=edit&gallery={$_GET['gallery']}' data-title='Gallery: Edit'><i class='fa fa-edit'></i> Edit</a>
					<div style='display: none;'>
						<form action='/gallery.do?do=view&gallery={$_GET['gallery']}' method='post' enctype='multipart/form-data' id='gallery-file-upl'>
							<input type='hidden' name='url' id='gallery-file-url' value=''>
							<input type='file' name='file' id='gallery-file'>
						</form>
					</div>
				";
			}
			$creator = $auth->getUsername($info["creator"]);
			
			$page->block("spanner", array("image" => false, "title" => "{$info['title']} by {$creator}", "content" => $info['description'], "href" => false, "text" => false));
			
			$media = $gallery->getCollectionMedia($_GET["gallery"]);
			if (count($media) === 0) {
				echo "
					<div class='callout callout-info'>
						<h4>Oh no!</h4>
						<p>There isn't anything here yet.</p>
					</div>
				";
			}
			else {
				echo "
					<div class='row uniform 75%'>
				";
				
				foreach (array_reverse($media, true) as $id => $data) {
					$srcEnc = base64_encode($data["source"]);
					echo "
						<div class='12u$(xsmall) 12u$(small) 3u'>
							<div class='gallery-thumb' style='background-image: url(/gallery.ps?t={$srcEnc});' data-featherlight='/gallery.ps?do=iview&image={$srcEnc}&g={$_GET['gallery']}&mid={$id}'>
							</div>
						</div>
					";
				}
				
				echo "
					</div>
				";
			}
		break;
		case "iview":
			echo "
				<img src='/gallery.ps?i={$_GET['image']}' alt='Image' style='max-width: 100%; max-height: 100vh;' />
			";
			$info = $gallery->getCollectionAttributes($_GET["g"]);
			if (defined("AUTH_UID") && $info["creator"] == AUTH_UID) {
				echo "<a class='label label-danger' href='/gallery.do?do=view&gallery={$_GET['g']}&rem={$_GET['mid']}' style='display: inline;'>Delete Image</a> | <a class='label label-warning' href='/gallery.do?do=view&gallery={$_GET['g']}&rotate={$_GET['mid']}' style='display: inline;'>Rotate Image</a>";
			}
		break;
		case "edit":
			requireLogin();
			$info = $gallery->getCollectionAttributes($_GET["gallery"]);
			if ($info["creator"] !== AUTH_UID) {
				echo "<script>location.href = '/gallery.do';</script>";
				echo "<p>Not allowed.</p>";
			}
			else {
				if (isset($_POST["cName"])) {
					$gallery->setCollectionAttribute($_GET["gallery"], "title", htmlentities($_POST["cName"]), SQLITE3_TEXT);
					$gallery->setCollectionAttribute($_GET["gallery"], "description", $_POST["cDesc"], SQLITE3_TEXT);
					$info = $gallery->getCollectionAttributes($_GET["gallery"]);
					echo "
						<div class='callout callout-success'>
							<p>You have successfully updated your collection.</p>
							<p><a href='/gallery.do?do=view&gallery={$_GET['gallery']}' class='btn btn-info content-link' data-title='Gallery'>Click here to continue</a></p>
						</div>
					";
				}
				echo "
					<div class='box box-primary'>
						<div class='box-header'>
							<h4 class='box-title'>Edit Collection</h4>
						</div>
						<div class='box-body'>
							<form action='/gallery.do?do=edit&gallery={$_GET['gallery']}' method='post' role='form' class='form'>
								<div class='form-group'>
									<label for='cName'>Collection Name</label>
									<input type='text' id='cName' name='cName' placeholder='(required)' value=\"" . htmlentities($info["title"]) . "\" class='form-control' />
								</div>
								<div class='form-group'>
									<label for='gallery-cDesc'>Collection Description</label>
									<textarea id='gallery-cDesc' name='cDesc' rows='6' class='form-control'>{$info['description']}</textarea>
								</div>
								<div class='form-group'>
									<input type='submit' value='Update' class='form-control btn btn-primary' />
								</div>
							</form>
						</div>
					</div>
				";
			}
		break;
		default:
			$collections = $gallery->getCollections();
			if (count($collections) === 0) {
				echo "
					<div class='callout callout-info'>
						<h4>Oh no!</h4>
						<p>There isn't anything here yet.</p>
					</div>
				";
			}
			else {
				echo "
					<div class='row uniform 50%'>
				";
				foreach ($collections as $id => $title) {
					$media = $gallery->getCollectionMedia($id);
					$info = $gallery->getCollectionAttributes($id);
					$creator = $auth->getUsername($info["creator"]);
					if (count($media) > 0) {
						$banner = array_pop($media);
						$bannerEnc = base64_encode($banner["source"]);
						echo "
							<div class='12u$(xsmall) 12u$(small) 4u'>
								<a href='/gallery.do?do=view&gallery={$id}'><div class='gallery-thumb' style='background-image: url(/gallery.ps?t={$bannerEnc});'>
									<span class='gallery-title'><b>{$title}</b> &mdash; {$creator}</span>
								</div></a>
							</div>
						";
					}
					else {
						echo "
							<div class='12u$(xsmall) 12u$(small) 4u'>
								<a href='/gallery.do?do=view&gallery={$id}'><div class='gallery-thumb'>
									<span class='gallery-title'><b>{$title}</b> &mdash; {$creator}</span>
								</div></a>
							</div>
						";
					}
				}
				echo "
					</div>
				";
			}
		break;
	}

	echo "
	</article>
	</div>
	</section>
	";

	if ((isset($_GET["do"]) && $_GET["do"] == "iview")) {
		
	}
	else {
		$page->block("footer", array("js" => array("https://cdnjs.cloudflare.com/ajax/libs/featherlight/1.7.0/featherlight.min.js", "/assets/js/bootstrap3-wysihtml5.min.js", "/assets/js/gallery.js")));
	}
}
?>
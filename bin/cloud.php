<?php
requireLogin();

function transferGen($resource) {
	return "/cloud.ps?v=" . base64_encode(fnEncrypt($resource . '|' . time()));
}

if (isset($_GET["v"])) {
	$v = @fnDecrypt(base64_decode($_GET["v"]));
	if ($v) {
		$timestamp = substr($v, strripos($v, "|") + 1);
		$file = substr($v, 0, strripos($v, "|"));
		$timedelta = time() - $timestamp;
		if ($timedelta <= 3600) {
			if (file_exists($file)) {
				$type = "application/octet-stream";
				$fext = fext($file);
				if (fextIsImage($fext)) $type = "image/{$fext}";
				if (fextIsVideo($fext)) $type = "video/{$fext}";
				if (fextIsMusic($fext)) $type = "audio/{$fext}";
				$page->block("streamfile", array("file" => $file, "type" => $type));
			}
			else {
				header("Location: {$file}");
			}
		}
		else {
			die("Expired link.");
		}
	}
	else {
		die("Invalid request.");
	}
}
else {
	$page->show("header");

	$page->block("spanner", array("image" => "images/bg-cloud.jpg", "title" => "upload files?", "content" => "Too lazy to use proper cloud storage?", "href" => false, "text" => false));
	
	$page->block("spanner", array("image" => false, "title" => "", "content" => "A really small (private) cloud storage for whatever you need.", "href" => "/cloud.do", "text" => "home"));
	
	echo "
	<section class='wrapper'>
	<div class='inner'>
	<article>
	";

	echo "
		<a href='/cloud.do?createNewFile' class='button alt'>New Text File</a>
		<a href='#' id='storage-upload-btn' class='button alt'>Upload</a> 
		<progress id='storage-file-upl-progress' value='0' max='1' style='display: none;'></progress>
		<br />
		<br />
		<div style='display: none;'>
			<form action='/cloud.do?upload' method='post' enctype='multipart/form-data' id='storage-file-upl-form'>
				<input type='file' name='file' id='storage-file-upl'>
			</form>
		</div>
	";

	$rootDir = "data/account/storage/" . AUTH_USER;
	if (!is_dir($rootDir)) mkdir($rootDir);

	if (isset($_GET["createNewFile"])) {
		if (isset($_POST["filename"]) && strlen($_POST["content"]) <= 1048576) {
			$basename = basename(cleanString($_POST["filename"]));
			if (fext($basename) !== "txt") $basename .= ".txt";
			
			file_put_contents($rootDir . "/{$basename}", $_POST["content"]);
			echo "<span class='label label-success'>{$basename} saved.</span><br />";
		}
		$src = "";
		$src_data = "";
		$file = "";
		if (isset($_GET["src"])) {
			$file = basename(cleanString($_GET["src"]));
		}
		if (file_exists("{$rootDir}/{$file}")) {
			$src = $file;
			$src_data = file_get_contents("{$rootDir}/{$file}");
		}
		echo "
			<form action='/cloud.do?createNewFile' method='post'>
				<div class='form-group'>
					<input type='text' class='form-control' placeholder='Filename' name='filename' value='{$src}' />
				</div>
				<div class='form-group'>
					<textarea class='form-control' placeholder='Content' name='content' rows='10' maxlength='1048576'>{$src_data}</textarea>
					<span class='label label-warning'><i class='fa fa-warning'></i> 1MB maximum</span>
				</div>
				<div class='form-group'>
					<input type='submit' class='btn btn-primary' value='Save' />
				</div>
			</form>
		";
	}
	elseif (isset($_GET["view"])) {
		$file = basename($_GET["view"]);
		$ax = transferGen("{$rootDir}/{$file}");
		if (file_exists("{$rootDir}/{$file}")) {
			switch (fext($file)) {
				case "txt":
					echo "
						<a href='/cloud.do?createNewFile&src={$file}' class='btn btn-xs btn-primary content-link' data-title='Storage: Edit/Clone'>Edit/Clone File</a>
						<a class='btn btn-xs btn-primary' href='{$ax}&download'>Download: {$file}</a>
						<div class='well'>
							<pre>";
					echo file_get_contents("{$rootDir}/{$file}");
					echo "</pre>
						</div>
					";
				break;
				case "jpg":
				case "bmp":
				case "png":
				case "gif":
					echo "
						<a class='btn btn-xs btn-primary' href='{$ax}&download'>Download: {$file}</a>
						<div class='well' style='text-align: center;'>
							<img src='{$ax}&hx=image/jpg' style='max-width: 100%;' data-featherlight='{$ax}&hx=image/jpg#.jpg' />
						</div>
					";
				break;
				default:
					echo "
						<a class='btn btn-xs btn-primary' href='{$ax}&download'>Download: {$file}</a>
						<div class='well'>
							<p>File preview not supported.</p>
						</div>
					";
				break;
			}
		}
		else {
			echo "
				<span class='label label-danger'>Oops! We could not find this file.</span>
			";
		}
	}
	elseif (isset($_GET["upload"])) {
		if (!empty($_FILES)) {
			$tmp_name = $_FILES["file"]["tmp_name"];
			$file = $_FILES["file"]["name"];
			$target = "{$rootDir}/{$file}";
			if (file_exists($target)) {
				unlink($target);
			}
			move_uploaded_file($_FILES["file"]["tmp_name"], $target);
		}
		
		$directory = array_diff(scandir($rootDir), array(".", ".."));
		
		echo "
			<table class='table table-dark'>
		";
		if (count($directory) > 0) foreach ($directory as $file) {
			$filesize = formatBytes(filesize("{$rootDir}/{$file}"));
			$lastedit = time_since(time() - filemtime("{$rootDir}/{$file}"));
			echo "
				<tr><td><i class='fa fa-file'></i> <a href='/cloud.do?view={$file}' class='content-link' data-title='{$file}'>{$file}</a> &mdash; {$filesize}, {$lastedit} ago</td></tr>
			";
		}
		echo "
			</table>
		";
	}
	else {
		$directory = array_diff(scandir($rootDir), array(".", ".."));
		
		echo "
			<table class='table table-dark'>
		";
		if (count($directory) > 0) foreach ($directory as $file) {
			$filesize = formatBytes(filesize("{$rootDir}/{$file}"));
			$lastedit = time_since(time() - filemtime("{$rootDir}/{$file}"));
			echo "
				<tr><td><i class='fa fa-file'></i> <a href='/cloud.do?view={$file}' class='content-link' data-title='{$file}'>{$file}</a> &mdash; {$filesize}, {$lastedit} ago</td></tr>
			";
		}
		echo "
			</table>
		";
	}

	echo "
	</article>
	</div>
	</section>
	";

	$page->block("footer", array("js" => array("https://cdnjs.cloudflare.com/ajax/libs/notify/0.4.0/notify.min.js", "/assets/js/storage.js")));	
}
?>
<?php

namespace App\View;

use App\App;
use App\Request;
use App\SessionManager;
use App\Utility\StringSanitizer;

class Renderer {

	private Request $request;
	private SessionManager $sessionManager;

	public function __construct( Request $request, SessionManager $sessionManager ) {
		$this->request = $request;
		$this->sessionManager = $sessionManager;
	}

	public function renderHeader( string $pageTitle ): string {
		return <<<HTML
				<!DOCTYPE html>
				<html lang="en">
				<head>
					<meta charset="UTF-8">
					<meta name="viewport" content="width=device-width, initial-scale=1.0">
					<title>$pageTitle</title>
					<link rel="stylesheet" href="https://design.wikimedia.org/style-guide/css/build/wmui-style-guide.min.css">
					<link rel="stylesheet" href="styles.css">
					<script src="main.js" defer></script>
				</head>
				<body>
		HTML;
	}

	public function renderContent( App $app ): string {
		$csrfToken = $this->sessionManager->getCsrfToken();
		$title = StringSanitizer::fullSanitize( $this->request->getGetData( 'title' ) ?? '' );
		$article = $app->fetchArticle( $this->request->getGetData( 'title' ) );
		$body = StringSanitizer::fullSanitize( $article ?? '' );
		return <<<HTML
					<div id="header" class="header">
						<a href="/">Article Editor</a>
						<div>{$app->getWordCount()} words written</div>
					</div>
					<div class="page">
						<div class="main">
							<h2>Create/Edit Article</h2>
							<p>Create a new article by filling out the fields below. Edit an article by typing the title in the title field, selecting it from the auto-complete list, and updating the text field.</p>
							<form action="index.php" method="post">
								<input name="title" type="text" placeholder="Article title..." value="$title" required>
								<br />
								<textarea name="body" placeholder="Article body..." required>$body</textarea>
								<br />
								<button type="submit" class="submit-button">Submit</button>
								<input type="hidden" name="csrf_token" value="$csrfToken">

							</form>
							<h2>Preview</h2>
							<h3>Title:</h3> <p>$title</p>
							<h3>Content:</h3> <p>$body</p>
							<h2>Articles</h2>
							<ul>
		HTML;
	}

	public function renderListOfArticles( App $app ): void {
		foreach ( $app->getListOfArticles() as $article ) {
			$escapedArticle = StringSanitizer::fullSanitize( $article );
			echo "<li><a href='index.php?title=$escapedArticle'>$escapedArticle</a></li>";
		}

		echo <<<HTML
					</ul>
				</div>
			</div>
			</body>
			</html>
		HTML;
	}
}

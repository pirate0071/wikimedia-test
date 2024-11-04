<?php

namespace App\View;

use App\App;
use App\Request;
use App\SessionManager;
use App\Utility\StringSanitizer;
use Gregwar\Captcha\CaptchaBuilder;

/**
 * The Renderer class is responsible for generating the HTML output for various sections of a web page.
 */
class Renderer
{

	/** @var Request */
	private Request $request;
	/** @var SessionManager */
	private SessionManager $sessionManager;
	/** @var CaptchaBuilder */
	private CaptchaBuilder $captchaBuilder;

	/**
	 * Constructs a new instance of the class.
	 *
	 * @param Request $request The request instance.
	 * @param SessionManager $sessionManager The session manager instance.
	 * @param CaptchaBuilder $captchaBuilder The captcha builder instance.
	 *
	 * @return void
	 */
	public function __construct(Request $request, SessionManager $sessionManager, CaptchaBuilder $captchaBuilder)
	{
		$this->request = $request;
		$this->sessionManager = $sessionManager;
		$this->captchaBuilder = $captchaBuilder;
	}

	/**
	 * Renders the header section of an HTML page.
	 *
	 * @param string $pageTitle The title of the page to be rendered in the header.
	 *
	 * @return string The complete HTML header section.
	 */
	public function renderHeader(string $pageTitle): string
	{
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

	/**
	 * Renders the content for the article editor page.
	 *
	 * @param App $app The application instance.
	 *
	 * @return string The rendered HTML content.
	 */
	public function renderContent(App $app): string
	{
		$csrfToken = $this->sessionManager->getCsrfToken();
		$title = StringSanitizer::fullSanitize($this->request->getGetData('title') ?? '');
		$article = $app->fetchArticle($this->request->getGetData('title'));
		$body = StringSanitizer::fullSanitize($article ?? '');
		// Build the captcha
		// Check if a CAPTCHA already exists in the session
		if ($this->sessionManager->getByKey('captcha_answer') == '') {
			// Build a new CAPTCHA if none exists in the session
			$this->captchaBuilder->build();
			$this->sessionManager->setCaptchaAnswer($this->captchaBuilder->getPhrase());
		} else {
			// Reuse the existing CAPTCHA
			$this->captchaBuilder->setPhrase($this->sessionManager->getByKey('captcha_answer'));
			$this->captchaBuilder->build();
		}
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
								<label for="captcha">Enter CAPTCHA:</label>
								<img src="{$this->captchaBuilder->inline()}" alt="CAPTCHA">
								<input type="text" name="captcha_answer" required>
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

	/**
	 * Renders a list of articles.
	 *
	 * @param App $app The application instance containing the list of articles.
	 *
	 * @return void
	 */
	public function renderListOfArticles(App $app): void
	{
		foreach ($app->getListOfArticles() as $article) {
			$escapedArticle = StringSanitizer::fullSanitize($article);
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

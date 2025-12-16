<?php

declare(strict_types=1);

namespace App\Core;

class View
{
  private static string $basePath = __DIR__ . '/../../views/';

  /**
   * Render a view file
   *
   * @param string $view   Path relative to views/, without .php
   *                       Example: "profile/index"
   * @param array  $data   Variables passed to view
   */

  public static function render(string $view, array $data = []): void
  {
    $viewFile = self::$basePath . $view . '.php';

    if (!file_exists($viewFile)) {
      throw new \RuntimeException("View not found: {$viewFile}");
    }

    // Make array keys available as variables
    // print_r($data);
    extract($data, EXTR_SKIP);

    require $viewFile;
    exit;
  }

  /**
   * Render a SPA entry point
   *
   * @param array $data  Variables to pass to the frontend JS (JSON)
   */
  public static function renderSPA(array $data = []): void
  {
    $indexFile = self::$basePath . 'index.php';

    if (!file_exists($indexFile)) {
      throw new \RuntimeException("SPA entry point not found: {$indexFile}");
    }

    // Encode data as JSON for JS
    $jsonData = json_encode($data, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP);

    // Serve HTML with embedded initial data
    $html = file_get_contents($indexFile);

    // Inject initial data before closing </body> tag
    $inject = "<script>window.__INITIAL_DATA__ = {$jsonData};</script></body>";
    $html = str_replace('</body>', $inject, $html);

    echo $html;
    exit;
  }

  /**
   * SPA error page
   */
  public static function error(int $code): void
  {
    http_response_code($code);
    $file = self::$basePath . "html/{$code}.php";

    if (file_exists($file)) {
      require $file;
    } else {
      echo "<h1>{$code}</h1>";
    }

    exit;
  }

  /**
   * Render legacy layout (optional)
   */
  public static function renderWithLayout(
    string $view,
    array $data = [],
    string $layout = 'layouts/main'
  ): void {
    $viewFile   = self::$basePath . $view . '.php';
    $layoutFile = self::$basePath . $layout . '.php';

    if (!file_exists($viewFile)) {
      throw new \RuntimeException("View not found: {$viewFile}");
    }

    if (!file_exists($layoutFile)) {
      throw new \RuntimeException("Layout not found: {$layoutFile}");
    }

    extract($data, EXTR_SKIP);

    ob_start();
    require $viewFile;
    $content = ob_get_clean();

    require $layoutFile;
    exit;
  }
}

<?php

require __DIR__ . '/../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(__DIR__ . "../");
$dotenv->load();


class MailHandler
{
  private string $name;
  private string $email;
  private string $message;

  public function __construct(string $name, string $email, string $message)
  {
    $this->email = filter_var($email, FILTER_SANITIZE_EMAIL);
    $this->name = htmlspecialchars($name, ENT_QUOTES, 'UTF-8');
    $this->message = htmlspecialchars($message, ENT_QUOTES, 'UTF-8');
  }

  public function send(): bool
  {
    $mail = new PHPMailer(true);

    try {
      $mail->isSMTP();
      $mail->Host = $_ENV["SMTP_HOST"];
      $mail->SMTPAuth = true;
      $mail->Username = $_ENV["SMTP_EMAIL"];
      $mail->Password = $_ENV["SMTP_PASS"];
      $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
      $mail->Port = intval($_ENV["SMTP_PORT"]);

      $mail->setFrom($this->email, $this->name);
      $mail->addAddress($_ENV["SMTP_EMAIL"], 'Admin');

      $mail->isHTML(true);
      $mail->Subject = "New Contact Form Submission - PHPMailer - PWEB B";
      $mail->Body = <<<EOF
                <p><strong>Email:</strong> {$this->email}</p><br><br>

                <p>New email is received from {$this->name}.</p>
                <p>{$this->message}</p>
            EOF;

      $mail->AltBody = "Name: {$this->name}\nEmail: {$this->email}\nMessage:\n{$this->message}";

      $mail->SMTPDebug = 2;
      $mail->Debugoutput = 'error_log';
      $mail->send();

      return true;
    } catch (Exception $e) {
      error_log("Mail error: " . $mail->ErrorInfo);
      return false;
    }
  }
}

class RequestHandler
{
  public static function handle(): void
  {
    header("Access-Control-Allow-Origin: *");
    header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
    header("Access-Control-Allow-Headers: Content-Type, X-Requested-With");

    if ($_SERVER["REQUEST_METHOD"] === 'OPTIONS') {
      http_response_code(200);
      exit();
    }

    if ($_SERVER["REQUEST_METHOD"] === 'GET') {
      echo "Hello, World!";
    }

    if ($_SERVER["REQUEST_METHOD"] === 'POST') {
      $name = $_POST['name'] ?? '';
      $email = $_POST['email'] ?? '';
      $message = $_POST['message'] ?? '';

      if (empty($name) || empty($email)) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'http_code' => '400', 'message' => 'Missing name or email.']);
        return;
      }

      $mailHandler = new MailHandler($name, $email, $message);

      if ($mailHandler->send()) {
        http_response_code(200);
        echo json_encode(['status' => 'success', 'http_code' => '200', 'message' => "<span style='color:green; font-weight:bold;'>
      	Thank you for contacting us, we will get back to you shortly.
      	</span>"]);
      } else {
        http_response_code(500);
        echo json_encode(['status' => 'error', 'http_code' => '500', 'message' => "<span style='color:red; font-weight:bold;'>
      	  Sorry! Your form submission is failed.
      	</span>"]);
      }
    } else {
      http_response_code(405);
      echo json_encode(['status' => 'error', 'http_code' => '405', 'message' => "<span style='color:red; font-weight:bold;'>
      	  Invalid Method!
      	</span>"]);
    }
  }
}

RequestHandler::handle();

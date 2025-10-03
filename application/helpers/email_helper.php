<?php
defined('BASEPATH') or exit('No direct script access allowed');


/* ======== Email Buttons =========== */

function email_call2action_red($url, $caption)
{
	return '<table>
					<tr>
						<td style="background-color: #cc0821; border-color: #cc0821; border: 2px solid #cc0821; padding: 10px; text-align: center;">
							<a style="display: block; color: #ffffff; font-size: 17px; text-decoration: none; font-size: 18px;" href="' . $url . '">'
		. $caption . ' &raquo;
							</a>
						</td>
					</tr>
				</table>';
}


function email_call2action_blue($url, $caption)
{
	return '<table>
					<tr>
						<td style="background-color: #0e67bf; border-color: #0e67bf; border: 2px solid #0e67bf; padding: 10px; text-align: center;">
							<a style="display: block; color: #ffffff; font-size: 17px; text-decoration: none; text-transform: capitalize;" href="' . $url . '">'
		. $caption .
		'</a>
						</td>
					</tr>
				</table>';
}


// Function to send email notification
function send_email_notification($CI, $email, $subject, $data = [], $email_type)
{
	$email_type = 'mail/' . $email_type;
	try {
		require_once 'application/third_party/mail.php';

		// Get a new PHPMailer instance
		$mail = getMailer();

		// Recipients
		$mail->addAddress($email);
		// $mail->AddEmbeddedImage('application/third_party/image/colored_logo.png', 'logo', 'colored_logo.png');
		$mail->isHTML(true); // Set email format to HTML
		$mail->Subject = $subject;

		// Render the view with the data array
		$body = $CI->load->view($email_type, $data, true);
		$mail->Body = $body;
		$mail->AltBody = $body; // Plain text alternative

		$mail->send(); // Send the email
	} catch (Exception $e) {
		log_message('error', 'Mail sending failed: ' . $mail->ErrorInfo);
	}
}

// Function to send bulk email notifications
function send_bulk_email_notification($CI, $emails = [], $subject, $data = [], $email_type)
{
	if (empty($emails) || !is_array($emails)) {
		log_message('error', 'No valid email addresses provided.');
		return;
	}

	$email_type = 'mail/' . $email_type;

	try {
		require_once 'application/third_party/mail.php';

		// Get a new PHPMailer instance
		$mail = getMailer();

		// Set email format to HTML and embed the logo once
		// $mail->AddEmbeddedImage('application/third_party/image/colored_logo.png', 'logo', 'colored_logo.png');
		$mail->isHTML(true);
		$mail->Subject = $subject;

		// Render the view with the data array
		$body = $CI->load->view($email_type, $data, true);
		$mail->Body = $body;
		$mail->AltBody = $body; // Plain text alternative

		// Loop through each email address and send the email
		foreach ($emails as $email) {
			if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
				// Clear previous recipients
				$mail->clearAddresses();
				$mail->addAddress($email);

				// Send the email
				if (!$mail->send()) {
					log_message('error', 'Mail sending failed to ' . $email . ': ' . $mail->ErrorInfo);
				}
			} else {
				log_message('error', 'Invalid email address: ' . $email);
			}
		}
	} catch (Exception $e) {
		log_message('error', 'Bulk mail sending failed: ' . $e->getMessage());
	}
}



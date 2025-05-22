<?php

namespace Tonkra\Referral\Helpers;

use App\Models\Customer;

class Helper
{
	public static function updatePhpLocale($locale, $section, array $entries, $file = "locale")
	{
		$filePath = lang_path("$locale/$file.php");

		$translations = file_exists($filePath) ? include $filePath : [];
		$translations[$section] = array_merge($translations[$section] ?? [], $entries);

		file_put_contents($filePath, "<?php\n\nreturn " . str_replace(["array (", ")"], ["[", "]"], var_export($translations, true)) . ";\n");
	}

	public static function updateConfigPermission(array $permissions, $file = "permissions")
	{
		$filePath = config_path("$file.php");

		$config = file_exists($filePath) ? include $filePath : [];

		// Merge new permissions while preserving existing ones
		$config = array_merge($config, $permissions);

		file_put_contents($filePath, "<?php\n\nreturn " . str_replace(["array (", ")"], ["[", "]"], var_export($config, true)) . ";\n");
	}

	public static function addPermissions(array $permissions): void
	{
		Customer::chunk(100, fn($customers) => $customers->each(
			fn($user) =>
			$user->update([
				'permissions' => json_encode(array_values(array_unique(
					array_merge((array) json_decode($user->permissions, true), $permissions)
				)))
			])
		));
	}


	public static function removePermissions(array $permissions): void
	{
		Customer::chunk(100, fn($customers) => $customers->each(
			fn($user) =>
			$user->update([
				'permissions' => json_encode(array_values(array_diff(
					(array) json_decode($user->permissions, true),
					$permissions
				)))
			])
		));
	}

	/**
	 * Update setting one line.
	 *
	 * @param array $data
	 */
	public static function setEnv(array $data)
	{
		$file = base_path('.env');
		$env = file_get_contents($file);

		foreach ($data as $key => $value) {
			// Handle boolean values
			if (is_bool($value)) {
				$value = $value ? 'true' : 'false';
			}
			// Handle string values (including CKEditor HTML content)
			elseif (is_string($value)) {
				// Escape newlines and preserve formatting
				$value = str_replace(["\r\n", "\n", "\r"], '\n', $value);
				// Escape double quotes
				$value = str_replace('"', '\"', $value);
				// Wrap in quotes
				$value = '"' . $value . '"';
			}

			// Update or add the key
			if (preg_match("/^$key=/m", $env)) {
				$env = preg_replace("/^$key=.*/m", "$key=$value", $env);
			} else {
				$env .= "\n$key=$value";
			}
		}

		file_put_contents($file, $env);
	}

	/**
	 * Sanitize HTML content for .env file storage
	 */
	public static function sanitizeForEnv(string $content): string
	{
		// 1. Convert newlines to special tokens
		$content = str_replace(["\r\n", "\n", "\r"], '{{NEWLINE}}', $content);

		// 2. Escape double quotes
		$content = str_replace('"', '\"', $content);

		// 3. Remove any existing backslashes that might cause issues
		$content = stripslashes($content);

		// 4. Optionally, you could HTML encode it
		$content = htmlspecialchars($content, ENT_QUOTES);

		return $content;
	}

	/**
	 * Convert .env stored content back to HTML
	 */
	public static function desanitizeFromEnv(string $content): string
	{
		// 1. Convert special tokens back to newlines
		$content = str_replace('{{NEWLINE}}', "\n", $content);

		// 2. Unescape double quotes
		$content = str_replace('\"', '"', $content);

		// 3. If you used htmlspecialchars, decode it
		$content = htmlspecialchars_decode($content, ENT_QUOTES);

		return $content;
	}
}

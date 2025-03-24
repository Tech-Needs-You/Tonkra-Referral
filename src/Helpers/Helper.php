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
			$value = is_bool($value) ? ($value ? 'true' : 'false') : (is_string($value) ? "\"$value\"" : $value);
			$env = preg_match("/^$key=/m", $env) ? preg_replace("/^$key=.*/m", "$key=$value", $env) : "$env\n$key=$value";
		}

		file_put_contents($file, $env);
	}
}

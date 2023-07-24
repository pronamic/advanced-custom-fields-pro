<?php

function line( $text = '' ) {
	echo $text, PHP_EOL;
}

function run( $command, &$result_code = null ) {
	line( $command );

	$last_line = system( $command, $result_code );

	line();

	return $last_line;
}

/**
 * ACF PRO license.
 */
$acf_pro_license = getenv( 'ACF_PRO_LICENSE' );

if ( empty( $acf_pro_license ) ) {
	echo 'ACF PRO license not defined in `ACF_PRO_LICENSE` environment variable.';

	exit( 1 );
}

/**
 * Request info.
 */
line( '::group::Check ACF' );

$url = 'https://connect.advancedcustomfields.com/v2/plugins/update-check';

$basename = 'advanced-custom-fields-pro/acf.php';

$data_plugins = [
	$basename => [
		'id'       => 'pro',
		'key'      => $acf_pro_license,
		'slug'     => 'advanced-custom-fields-pro',
		'basename' => $basename,
		'version'  => '1.0.0',
	]
];

$data_wp = [
	'wp_name' => 'acf',
];

$data_acf = [
	'acf_version' => '1.0.0',
	'acf_pro'     => true,
	'block_count' => 0,
];

$data = run(
	sprintf(
		'curl --data %s --data %s --data %s --request POST %s',
		escapeshellarg( 'plugins=' . json_encode( $data_plugins ) ),
		escapeshellarg( 'wp=' . json_encode( $data_wp ) ),
		escapeshellarg( 'acf=' . json_encode( $data_acf ) ),
		escapeshellarg( $url )
	)
);

$result = json_decode( $data );

var_dump( $result );

if ( ! is_object( $result ) ) {
	throw new Exception(
		sprintf(
			'Unknow response from: %s.',
			$url 
		)
	);

	exit( 1 );
}

if ( ! property_exists( $result, 'plugins' ) ) {
	echo 'No plugins';

	exit( 1 );
}

$plugins = $result->plugins;

if ( ! property_exists( $plugins, $basename ) ) {
	echo 'No plugin';

	exit( 1 );
}

$plugin = $plugins->{$basename};

if ( ! property_exists( $plugin, 'new_version' ) ) {
	echo 'Unknown version';

	exit( 1 );
}

$version = $plugin->new_version;

$url = $plugin->package;

line(
	sprintf(
		'ACF Version: %s',
		$version
	)
);

line(
	sprintf(
		'ACF ZIP URL: %s',
		$url
	)
);

line( '::endgroup::' );

/**
 * Files.
 */
$work_dir = tempnam( sys_get_temp_dir(), '' );

unlink( $work_dir );

mkdir( $work_dir );

$archives_dir = $work_dir . '/archives';
$plugins_dir  = $work_dir . '/plugins';

mkdir( $archives_dir );
mkdir( $plugins_dir );

$plugin_dir = $plugins_dir . '/advanced-custom-fields-pro';

$zip_file = $archives_dir . '/advanced-custom-fields-pro-' . $version . '.zip';

/**
 * Download ZIP.
 */
line( '::group::Download ACF' );

run(
	sprintf(
		'curl %s --output %s',
		escapeshellarg( $url ),
		$zip_file
	)
);

line( '::endgroup::' );

/**
 * Unzip.
 */
line( '::group::Unzip ACF' );

run(
	sprintf(
		'unzip %s -d %s',
		escapeshellarg( $zip_file ),
		escapeshellarg( $plugins_dir )
	)
);

line( '::endgroup::' );

/**
 * Synchronize.
 * 
 * @link http://stackoverflow.com/a/14789400
 * @link http://askubuntu.com/a/476048
 */
line( '::group::Synchronize ACF' );

run(
	sprintf(
		'rsync --archive --delete-before --exclude=%s --exclude=%s --exclude=%s --verbose %s %s',
		escapeshellarg( '.git' ),
		escapeshellarg( '.github' ),
		escapeshellarg( 'composer.json' ),
		escapeshellarg( $plugin_dir . '/' ),
		escapeshellarg( '.' )
	)
);

line( '::endgroup::' );

/**
 * Git user.
 * 
 * @link https://github.com/roots/wordpress/blob/13ba8c17c80f5c832f29cf4c2960b11489949d5f/bin/update-repo.php#L62-L67
 */
run(
	sprintf(
		'git config user.email %s',
		escapeshellarg( 'support@advancedcustomfields.com' )
	)
);

run(
	sprintf(
		'git config user.name %s',
		escapeshellarg( 'ACF' )
	)
);

/**
 * Git commit.
 * 
 * @link https://git-scm.com/docs/git-commit
 */
run( 'git add --all' );

run(
	sprintf(
		'git commit --all -m %s',
		escapeshellarg(
			sprintf(
				'Updates to %s',
				$version
			)
		)
	)
);

run( 'git config --unset user.email' );
run( 'git config --unset user.name' );

run( 'gh auth status' );

run( 'git push origin main' );

/**
 * GitHub release view.
 */
$tag = 'v' . $version;

run(
	sprintf(
		'gh release view %s',
		$tag
	),
	$result_code
);

$release_not_found = ( 1 === $result_code );

/**
 * GitHub release.
 * 
 * @todo https://memberpress.com/wp-json/wp/v2/pages?slug=change-log
 * @link https://cli.github.com/manual/gh_release_create
 */
if ( $release_not_found ) {
	run(
		sprintf(
			'gh release create %s %s --title %s',
			$tag,
			$zip_file,
			$version
		)
	);
}

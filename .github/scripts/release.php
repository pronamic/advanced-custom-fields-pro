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
 * Pronamic ACF authorization.
 */
$pronamic_acf_authorization = getenv( 'PRONAMIC_ACF_AUTHORIZATION' );

if ( empty( $pronamic_acf_authorization ) ) {
	echo 'Pronamic ACF authorization not defined in `PRONAMIC_ACF_AUTHORIZATION` environment variable.';

	exit( 1 );
}

$header_authorization = 'Authorization: Bearer ' . $pronamic_acf_authorization;

/**
 * Request info.
 */
line( '::group::Check ACF' );

$url = 'https://acf-connect.pronamic.directory/version';

$data = run(
	sprintf(
		'curl --header %s --request GET %s',
		escapeshellarg( $header_authorization ),
		escapeshellarg( $url )
	)
);

$result = json_decode( $data );

if ( ! is_object( $result ) ) {
	throw new Exception(
		sprintf(
			'Unknow response from: %s.',
			$url 
		)
	);

	exit( 1 );
}

if ( ! property_exists( $result, 'version' ) ) {
	echo 'No version';

	exit( 1 );
}

$version = $result->version;

line(
	sprintf(
		'ACF Version: %s',
		$version
	)
);

line( '::endgroup::' );

/**
 * GitHub release view.
 */
line( '::group::Check GitHub release' );

$tag = 'v' . $version;

run(
	sprintf(
		'gh release view %s',
		$tag
	),
	$result_code
);

$release_found = ( 0 === $result_code );

line( '::endgroup::' );

if ( $release_found ) {
	return;
}

/**
 * Download.
 */
$url = 'https://acf-connect.pronamic.directory/download';

line(
	sprintf(
		'ACF ZIP URL: %s',
		$url
	)
);

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
		'curl --header %s --request GET %s --output %s',
		escapeshellarg( $header_authorization ),
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
 * GitHub release.
 * 
 * @todo https://memberpress.com/wp-json/wp/v2/pages?slug=change-log
 * @link https://cli.github.com/manual/gh_release_create
 */
run(
	sprintf(
		'gh release create %s %s --title %s',
		$tag,
		$zip_file,
		$version
	)
);

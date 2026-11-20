declare module '@wpengine/bds-react/fonts.css' {}
declare module '@wpengine/bds-react/styles.css' {}

declare module '*.module.css' {
	const classes: Readonly< Record< string, string > >;
	export default classes;
}

declare module '*.css' {}

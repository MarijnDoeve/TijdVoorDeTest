// Ambient declarations for the parts of the AssetMapper toolchain that Deno's
// module resolution doesn't otherwise understand: CSS/SCSS side-effect imports
// (handled by AssetMapper at build time, not a JS module) and the Symfony
// stimulus-bundle loader (a local vendor/ file, not an npm package).

declare module '*.css';
declare module '*.scss';

declare module '@symfony/stimulus-bundle' {
    export function startStimulusApp(): unknown;
}

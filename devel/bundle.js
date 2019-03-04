const { 
    Bundler, 
    generateShellMergeStrategy, 
    generateSharedDepsMergeStrategy, 
    generateCountingSharedBundleUrlMapper
} = require('polymer-bundler');

const { 
    Analyzer, 
    FsUrlLoader, 
    FsUrlResolver,
    PackageUrlResolver 
} = require('polymer-analyzer');

const path = require('path');
const util = require('polymer-bundler/lib/url-utils');
const root = util.resolvePath(util.ensureTrailingSlash('../'));
const shell = 'devel/src/kct-app.html';

const urlLoader = new FsUrlLoader(root);
const urlResolver = new FsUrlResolver(root);
const moduleResolution = undefined;

const analyzer = new Analyzer({ 
    urlResolver: urlResolver, 
    urlLoader: urlLoader 
});

const bundler = new Bundler({
    analyzer: analyzer,
    excludes: [],
    inlineScripts: true,
    inlineCss: true,
    rewriteUrlsInTemplates: false,
    stripComments: true,
    urlMapper: generateCountingSharedBundleUrlMapper(analyzer.resolveUrl('bundle_'))
    //strategy: generateSharedDepsMergeStrategy(3),
    // urlMapper: generateCountingSharedBundleUrlMapper('devel/build/bundle_')
    // strategy: generateShellMergeStrategy(analyzer.resolveUrl(shell), 2)
    // strategy: generateShellMergeStrategy(shell)
});

bundler.generateManifest([
    shell
    //'devel/src/layouts/main-layout.html',
]).then(manifest => {
    bundler.bundle(manifest).then((result) => {
        for(const [url, doc] of result.documents) {
            console.log(url)
            // const out = util.resolvePath('devel/build', bundler.analyzer.urlResolver.relative(url));
            // const dir = path.dirname(out);
            // console.log(dir);
            // console.log(out);
        }
    }).catch(err => {
        console.log(err)
    })
});
import esbuild from 'esbuild'
import fs from 'fs'

const isDev = process.argv.includes('--dev')

async function compile(options) {
    const context = await esbuild.context(options)

    if (isDev) {
        await context.watch()
    } else {
        await context.rebuild()
        await context.dispose()
    }
}

function convertBytes(bytes, options = {}) {
    const { useBinaryUnits = false, decimals = 2 } = options;

    if (decimals < 0) {
        throw new Error(`Invalid decimals ${decimals}`);
    }

    const base = useBinaryUnits ? 1024 : 1000;
    const units = useBinaryUnits
        ? ["Bytes", "KiB", "MiB", "GiB", "TiB", "PiB", "EiB", "ZiB", "YiB"]
        : ["Bytes", "KB", "MB", "GB", "TB", "PB", "EB", "ZB", "YB"];

    const i = Math.floor(Math.log(bytes) / Math.log(base));

    return `${(bytes / Math.pow(base, i)).toFixed(decimals)} ${units[i]}`;
}

const defaultOptions = {
    define: {
        'process.env.NODE_ENV': isDev ? `'development'` : `'production'`,
    },
    bundle: true,
    mainFields: ['module', 'main'],
    platform: 'neutral',
    sourcemap: isDev ? 'inline' : false,
    sourcesContent: isDev,
    treeShaking: true,
    target: ['es2020'],
    minify: !isDev,
    plugins: [{
        name: 'watchPlugin',
        setup: function (build) {
            build.onStart(() => {
                console.log(`Build started at ${new Date(Date.now()).toLocaleTimeString()}: ${build.initialOptions.outfile}`)
            })

            build.onEnd((result) => {
                if (result.errors.length > 0) {
                    console.log(`Build failed at ${new Date(Date.now()).toLocaleTimeString()}: ${build.initialOptions.outfile}`, result.errors)
                } else {
                    console.log(`Build finished at ${new Date(Date.now()).toLocaleTimeString()}: ${build.initialOptions.outfile}`)
                }
            })
        }
    }],
}

compile({
    ...defaultOptions,
    entryPoints: ['./resources/js/index.js'],
    outfile: './resources/dist/mason.js',
}).then(() => {
    if (!isDev) {
        fs.stat('./resources/dist/mason.js', (err, stats) => {
            if (err) {
                console.log(err);
                return;
            }

            console.log(`Total file size: ${convertBytes(stats.size)}`);
        })
    }
})

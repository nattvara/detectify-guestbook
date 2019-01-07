const { VueLoaderPlugin } = require('vue-loader');
const path = require('path');
const env = process.env.NODE_ENV;

module.exports = {
    entry: [
        path.join(__dirname, 'resources', 'js', 'app.js'),
        path.join(__dirname, 'resources', 'sass', 'app.scss'),
        path.join(__dirname, 'resources', 'sass', 'element-theme', 'index.css'),
    ],
    mode: env,
    output: {
        publicPath: '/',
    },
    module: {
        rules: [
            {
                test: /\.js$/,
                loader: 'babel-loader',
                include: [path.join(__dirname, 'resources', 'js')],
            },
            {
                test: /\.vue$/,
                loader: 'vue-loader'
            },
            {
                test: /\.scss$/,
                use: [
                    'vue-style-loader',
                    'css-loader',
                    'sass-loader'
                ],
            },
            {
                test: /(\.css$)/,
                loaders: [
                    'style-loader',
                    'css-loader',
                ]
            },
            {
                test: /\.(png|woff|woff2|eot|ttf|svg)$/,
                loader: 'url-loader?limit=100000'
            }
        ]
    },
    resolve: {
        alias: {
            'vue$': 'vue/dist/vue.esm.js',
            '@': path.resolve('resources/sass')
        },
        extensions: ['*', '.js', '.vue', '.json']
    },
    plugins: [
        new VueLoaderPlugin(),
    ],
    watchOptions: {
        poll: true
    }
}

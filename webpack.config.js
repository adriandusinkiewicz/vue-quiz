const path = require('path');
const VueLoaderPlugin = require('vue-loader/lib/plugin');
var ExtractTextPlugin = require("extract-text-webpack-plugin");

const extractCSS = new ExtractTextPlugin("../css/app.css");
const extractSCSS = new ExtractTextPlugin("../css/app.css");

var config = {
    mode: 'development',
    resolve: {
        alias: {
            'vue$': 'vue/dist/vue.esm.js'
        },
        extensions: ['*', '.js', '.vue', '.json']
    },
    module: {
        rules: [{
                test: /\.vue$/,
                loader: 'vue-loader'
            },
            {
                test: /\.js$/,
                loader: 'babel-loader'
            },
            {
                test: /\.css$/,
                loader: ExtractTextPlugin.extract({
                    use: "css-loader!sass-loader",
                    fallback: "vue-style-loader?minimize"
                }),
            },
            {
                test: /\.scss$/,
                loader: ExtractTextPlugin.extract({
                    use: "css-loader!sass-loader",
                    fallback: "vue-style-loader?minimize"
                }),
            }
        ],
    },
    plugins: [
        new VueLoaderPlugin(),
        extractCSS,
        extractSCSS
    ]
};

var publicConfig = Object.assign({}, config, {
    name: "public",
    entry: "./src/public/main.js",
    output: {
        filename: "app.js",
        path: path.resolve(__dirname, 'app/public/js')
    }
});
var adminConfig = Object.assign({}, config, {
    name: "admin",
    entry: "./src/admin/main.js",
    output: {
        filename: "app.js",
        path: path.resolve(__dirname, 'app/admin/js')
    }
});

// Return Array of Configurations
module.exports = [
    publicConfig, adminConfig
];
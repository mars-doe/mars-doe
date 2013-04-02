// Shader Loader from 100,000 Stars http://workshop.chromeexperiments.com/stars/

var shaderList = ['shaders/lavashader'];

function loadShaders(list, callback) {
    var shaders = {};
    var expectedFiles = list.length * 2;
    var loadedFiles = 0;

    function makeCallback(name, type) {
        return function (data) {
            if (shaders[name] === undefined) {
                shaders[name] = {};
            }
            shaders[name][type] = data;
            loadedFiles++;
            if (loadedFiles == expectedFiles) {
                callback(shaders);
            }
        };
    }
    for (var i = 0; i < list.length; i++) {
        var vertexShaderFile = list[i] + '.vsh';
        var fragmentShaderFile = list[i] + '.fsh';
        var splitted = list[i].split('/');
        var shaderName = splitted[splitted.length - 1];
        $(document)
            .load(vertexShaderFile, makeCallback(shaderName, 'vertex'));
        $(document)
            .load(fragmentShaderFile, makeCallback(shaderName, 'fragment'));
    }
}

// function loadShaders(list, callback) {
//     var shaders = {};
//     var expectedFiles = list.length * 2;
//     var loadedFiles = 0;

//     function makeCallback(name, type) {
//         return function (data) {
//             if (shaders[name] === undefined) {
//                 shaders[name] = {};
//             }
//             shaders[name][type] = data;
//             loadedFiles++;
//             if (loadedFiles == expectedFiles) {
//                 callback(shaders);
//             }
//         };
//     }
//     for (var i = 0; i < list.length; i++) {
//         var vertexShaderFile = list[i] + '.vsh';
//         var fragmentShaderFile = list[i] + '.fsh';
//         var splitted = list[i].split('/');
//         var shaderName = splitted[splitted.length - 1];
//         $(document)
//             .load(vertexShaderFile, makeCallback(shaderName, 'vertex'));
//         $(document)
//             .load(fragmentShaderFile, makeCallback(shaderName, 'fragment'));
//     }
// }

// var textureLoader = new THREE.ImageLoader();

// function loadTextures( eph, callback )
// textureLoader.addEventListener( 'load', function ( event ) {

//     planetTexture.image = event.content;
//     planetTexture.needsUpdate = true;

// } );

//     textureLoader.load( imageTexture );
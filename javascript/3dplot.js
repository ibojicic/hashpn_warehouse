$(function ()
{   
    if (!Detector.webgl)
        Detector.addGetWebGLMessage();

    var container, stats;
    var camera, scene, renderer, particles, geometry, materials = [], parameters, i, h, color, size;
    //var mouseX = 0, mouseY = 0;

    var windowHalfX = window.innerWidth / 2;
    var windowHalfY = window.innerHeight / 2;
    var maxDistance = 5000;
    var YmaxDist, ZmaxDist;
    var pixTopc = 1930;
    // 1900 px = 8kpc

    init();

    animate();



    function init() {


        var datasets = jQuery.parseJSON(
                jQuery.ajax({
                    url: 'jsongpne.json',
                    async: false,
                    dataType: 'json'
                }).responseText
                );
                
                alert('test');

        container = document.createElement('div');
        document.body.appendChild(container);

        camera = new THREE.PerspectiveCamera(75, window.innerWidth / window.innerHeight, 1, 30000);
        camera.position.z = 1000;
        camera.position.y = -2900;


        scene = new THREE.Scene();
        // fog, distant objects are less visible
        scene.fog = new THREE.FogExp2(0x000000, 0.00003);




        geometry = new THREE.Geometry();

        $.each(datasets, function (key, val)
        {
            var vertex = new THREE.Vector3();

            vertex.x = val.d * Math.sin((-val.glon) * Math.PI / 180) * Math.cos((val.glat) * Math.PI / 180) * pixTopc / 8;
            vertex.y = val.d * Math.cos((-val.glon) * Math.PI / 180) * Math.cos((val.glat) * Math.PI / 180) * pixTopc / 8 - pixTopc;
            vertex.z = val.d * Math.sin((val.glat) * Math.PI / 180) * 1900 / 8;

            geometry.vertices.push(vertex);

        });

        parameters = [
            //[ [0.95, 1, 0.5], 4 ],
            //[ [0.90, 1, 0.5], 3 ],
            //[ [0.85, 1, 0.5], 20 ],
            [[0.96, 0.7, 0.5], 30]
        ];

        for (i = 0; i < parameters.length; i++) {
            color = parameters[i][0];
            size = parameters[i][1];
            materials[i] = new THREE.PointCloudMaterial({size: size});
            h = (360 * (color[0]) % 360) / 360;


            materials[i].color.setHSL(h, color[1], color[2]);
            particles = new THREE.PointCloud(geometry, materials[i]);
            scene.add(particles);
        }

        renderer = new THREE.WebGLRenderer();
        renderer.setPixelRatio(window.devicePixelRatio);
        renderer.setSize(window.innerWidth, window.innerHeight);
        container.appendChild(renderer.domElement);

        stats = new Stats();
        stats.domElement.style.position = 'absolute';
        stats.domElement.style.top = '0px';
        container.appendChild(stats.domElement);
        controls = new THREE.OrbitControls(camera, renderer.domElement);

        ///////////
        // FLOOR //
        ///////////

        // note: 4x4 checkboard pattern scaled so that each square is 25 by 25 pixels.
        var floorTexture = new THREE.ImageUtils.loadTexture('images/MWspitzer.jpg');
        //floorTexture.wrapS = floorTexture.wrapT = THREE.RepeatWrapping; 
        //floorTexture.repeat.set( 10, 10 );
        // DoubleSide: render texture on both sides of mesh
        var floorMaterial = new THREE.MeshBasicMaterial({map: floorTexture, side: THREE.DoubleSide, transparent: true, opacity: 0.90});
        var floorGeometry = new THREE.PlaneGeometry(10000, 10000, 1, 1);
        var floor = new THREE.Mesh(floorGeometry, floorMaterial);
        //floor.position.y = 0.5;
        //floor.rotation.x = Math.PI;
        scene.add(floor);


    }

    function animate() {
        requestAnimationFrame(animate);
        render();
        controls.update();
        stats.update();
    }

    function render() {
        camera.lookAt(scene.position);
        renderer.render(scene, camera);

    }
});

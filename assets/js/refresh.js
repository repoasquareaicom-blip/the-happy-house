

function refreshImages(){
	var bgrefresh = document.getElementsByClassName('bgrefresh');

	for(var i=0; i<bgrefresh.length;i++){
		var t = bgrefresh[i].getAttribute('data-img-type');
		var img = bgrefresh[i].getAttribute('data-bg');
		if(t=="bg"){
			bgrefresh[i].style.background = "url('"+ img+"?"+Math.random() +"') no-repeat center center/cover;";
		}
		else if(t=="src"){
			bgrefresh[i].setAttribute('src', img+"?"+Math.random());
		}
	}
}

refreshImages();
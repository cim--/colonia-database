if ( 'speechSynthesis' in window ) {
    $(function() {

	var sequence = RadioSequenceNumber+1;
	
	var tts = window.speechSynthesis;

	var manualpause = false;
	
	var ctrl = {};

	ctrl.RadioQueue = function RadioQueue() {
	    var text = $('#speechbox').text();
	    var words = text.replace(/\s+/g, " ").split(/\./);
	    console.log(words);
	    for (var i=0;i<words.length;i++) {
		var speech = new SpeechSynthesisUtterance(words[i]+".");
		console.log("Queueing: "+words[i]);
		tts.speak(speech);
	    }
	};

	ctrl.PlayRadio = function PlayRadio() {
	    console.log("Play");
	    tts.resume();
	    manualpause = false;
	}

	ctrl.PauseRadio = function PlayRadio() {
	    console.log("Pause");
	    tts.pause();
	    /* For some reason .pause() doesn't seem to actually do
	     * anything in most browsers. This will at least let it
	     * stop at the end of the current queue rather than
	     * loading the next page! */
	    manualpause = true;
	}

	
	ctrl.Monitor = function Monitor() {
	    if (!manualpause && !tts.paused && !tts.pending && !tts.speaking) {
		// could speak, but queue is empty
		$.get("/api/article/"+sequence, function (data) {
		    // replace contents
		    console.log("Loading "+sequence);
		    $('#speechbox').html(data);
		    setTimeout(ctrl.RadioQueue(),3000); // breathe between articles
		    sequence++;
		    setTimeout(ctrl.Monitor, 10000);
		});

	    } else {
		// check again later
		setTimeout(ctrl.Monitor, 1000);
	    }
	}
	
	$('#speechbox').each(function() {
	    ctrl.PauseRadio();
	    ctrl.RadioQueue();

	    $('#pausebutton').click(ctrl.PauseRadio);
	    $('#playbutton').click(ctrl.PlayRadio);

	    ctrl.Monitor();
	});
	
    });
}

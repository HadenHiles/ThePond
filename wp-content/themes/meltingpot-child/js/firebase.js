jQuery(document).ready(function() {
	var a_key            = "AIzaSyCoSWim4GptSro0gly6dN8dClVQMcxeCbA";
	var pid              = "the-pond-app";
	var firebaseConfig    = {
	    apiKey: a_key,
	    authDomain: pid+'.firebaseapp.com',
	    databaseURL: 'https://'+pid+'.firebaseio.com',
	    projectId: pid,
	    storageBucket: ''
    };

    // Initialize Firebase
    if (!firebase.apps.length) {
    	firebase.initializeApp(firebaseConfig);
    }

    // THIS WORKS!
    // var db = firebase.firestore();
    
    // db.collection("test").add({
    //     name: "Test"
    // })
    // .then(function(docRef) {
    //     console.log("Document written with ID: ", docRef.id);
    // })
    // .catch(function(error) {
    //     console.error("Error adding document: ", error);
    // });
});
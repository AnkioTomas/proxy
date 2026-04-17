(function() {
    if (location.pathname !== "/@login") return;
    waitForElement("[name=username]").then(input => {
        simulateTyping(document.querySelector('[name=username]'), "[username]")
        simulateTyping(document.querySelector('[name=password]'), "[password]")
        simulateHumanClick(document.querySelectorAll(".hope-button")[1]);
    });
})();
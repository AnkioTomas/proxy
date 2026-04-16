(function() {
    waitForElement("[name=username]").then(input => {
        simulateTyping(document.querySelector('[name=username]'), "[username]")
        simulateTyping(document.querySelector('[name=password]'), "[password]")
        simulateHumanClick(document.querySelector('.el-checkbox__label'));
        simulateHumanClick(document.querySelector('.el-form-item__content button'));
    });
})();
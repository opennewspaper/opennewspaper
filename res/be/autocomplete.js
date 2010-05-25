// Subclass of Ajax.Autocompleter that enables the selection of elements that are not returned from the server.
// 'updateChoices' is copied from Ajax.Autocompleter, only 'this.index = -1' was changed.
// 'selectEntry' overrides original. When no suggestion is selected,
// a new element is created from the content of the input-field. 
var NonSelectingAutocompleter = Class.create(Ajax.Autocompleter, {
    updateChoices: function(choices) {
        if(!this.changed && this.hasFocus) {
          this.update.innerHTML = choices;
          Element.cleanWhitespace(this.update);
          Element.cleanWhitespace(this.update.down());

          if(this.update.firstChild && this.update.down().childNodes) {
            this.entryCount =
              this.update.down().childNodes.length;
            for (var i = 0; i < this.entryCount; i++) {
              var entry = this.getEntry(i);
              entry.autocompleteIndex = i;
              this.addObservers(entry);
            }
          } else {
            this.entryCount = 0;
          }

          this.stopIndicator();
          this.index = -1; //changed from 0 to -1 so that by default no entry is selected

          if(this.entryCount==1 && this.options.autoSelect) {
            this.selectEntry();
            this.hide();
          } else {
            this.render();
          }
        }
      },

      selectEntry: function() {
        this.active = false;
        if(this.index > 0) {
            this.updateElement(this.getCurrentEntry());
        } else {
            var pseudoElement = document.createElement('li');
            pseudoElement.innerHTML = this.element.value;
            this.updateElement(pseudoElement);
        }
      }
});
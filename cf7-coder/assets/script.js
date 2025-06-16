'use strict';
(function ($) {
  $(function () {
    const checkboxSelector = '#wpcf7_codemiror_dark';
    const textareaId = 'wpcf7-form';
    const $textarea = $('#' + textareaId);
    const editorSettings = wp.codeEditor.defaultSettings ? _.clone(wp.codeEditor.defaultSettings) : {};

    const codemirrorGen = {
      indentUnit: 4,
      indentWithTabs: true,
      inputStyle: 'contenteditable',
      lineNumbers: true,
      lineWrapping: true,
      matchBrackets: true,
      styleActiveLine: true,
      continueComments: true,
      extraKeys: {
        'Ctrl-Space': 'autocomplete',
        'Ctrl-/': 'toggleComment',
        'Cmd-/': 'toggleComment',
        'Alt-F': 'findPersistent',
        'Ctrl-F': 'findPersistent',
        'Cmd-F': 'findPersistent',
        'Ctrl-D': function (cm) {
          cm.execCommand('duplicateLine');
        },
        'Cmd-D': function (cm) {
          cm.execCommand('duplicateLine');
        },
      },
      direction: 'ltr',
      gutters: ['CodeMirror-lint-markers', 'CodeMirror-linenumbers'],
      mode: 'htmlmixed',
      lint: true,
      autoCloseBrackets: true,
      autoCloseTags: true,
      matchTags: { bothTags: true },
      tabSize: 2,
    };

    let editorHTML = null;

    // Функція ініціалізації CodeMirror з підтримкою теми
    function initEditor(isDark) {
      const finalSettings = Object.assign({}, editorSettings, {
        codemirror: Object.assign({}, editorSettings.codemirror || {}, codemirrorGen, {
          theme: isDark ? 'material' : 'default',
        }),
      });

      return wp.codeEditor.initialize(textareaId, finalSettings);
    }

    // Ініціалізуємо редактор, якщо textarea існує
    if ($textarea.length) {
      const isDark = $(checkboxSelector).is(':checked');
      editorHTML = initEditor(isDark);

      editorHTML.codemirror.on('change', function () {
        document.getElementById(textareaId).value = editorHTML.codemirror.getValue();
      });

      $('#informationdiv_coder').insertAfter('#informationdiv').show();

      // Зміна теми при кліку на чекбокс
      $(checkboxSelector).on('change', function () {
        const newIsDark = $(this).is(':checked');
        const currentValue = editorHTML.codemirror.getValue();

        editorHTML.codemirror.toTextArea();
        editorHTML = initEditor(newIsDark);
        editorHTML.codemirror.setValue(currentValue);

        editorHTML.codemirror.on('change', function () {
          document.getElementById(textareaId).value = editorHTML.codemirror.getValue();
        });
      });
    }

    // Очікуємо wpcf7.taggen і перевизначаємо insert
    function waitForWpcf7Taggen(callback, attempt = 0) {
      if (typeof wpcf7 !== 'undefined' && wpcf7.taggen && typeof wpcf7.taggen.insert === 'function') {
        callback();
      } else if (attempt < 20) {
        setTimeout(() => waitForWpcf7Taggen(callback, attempt + 1), 100);
      } else {
        console.warn('wpcf7.taggen.insert не знайдено.');
      }
    }

    waitForWpcf7Taggen(() => {
      const originalInsert = wpcf7.taggen.insert;

      wpcf7.taggen.insert = function (content) {
        if (editorHTML && editorHTML.codemirror) {
          const cursor = editorHTML.codemirror.getCursor();
          editorHTML.codemirror.replaceRange(content, cursor);
          document.getElementById(textareaId).value = editorHTML.codemirror.getValue();
        }
        originalInsert.apply(this, arguments);
      };
    });
  });
})(jQuery);
import FileChooser from '../../file-chooser/file-choose';
import { chooserUiOpen, chooserUiClose, showChooserType } from '../widget/chooser-ui.js';

export default class Audio {
  constructor() {
    showChooserType($('[name="ext[mediaId]"]'));
    this.initStep2form();
    this.autoValidatorLength();
    this.initfileChooser();
  }

  initStep2form() {
    var $step2_form = $('#step2-form');
    var validator = $step2_form.data('validator');

    $step2_form.validate({
      groups: {
        nameGroup: 'minute second'
      },
      rules: {
        title: {
          required: true,
          maxlength: 50,
          trim: true,
          course_title: true,
        },
        minute: 'required unsigned_integer unsigned_integer',
        second: 'required second_range unsigned_integer',
        'ext[mediaId]': 'required'
      },
      messages: {
        minute: {
          required: Translator.trans('请输入时长'),
          unsigned_integer: Translator.trans('时长必须大于0')
        },
        second: {
          required: Translator.trans('请输入时长'),
          second_range: Translator.trans('秒只能在0-59之间'),
          unsigned_integer: Translator.trans('时长必须大于0')
        },
        'ext[mediaId]': Translator.trans('请上传或选择%display%')
      }
    });

  }

  autoValidatorLength() {
    $(".js-length").blur(function () {
      let validator = $("#step2-form").data('validator');
      if (validator && validator.form()) {
        const minute = parseInt($('#minute').val()) | 0;
        const second = parseInt($('#second').val()) | 0;
        $("#length").val(minute * 60 + second);
      }
    });
  }

  initfileChooser() {
    const fileChooser = new FileChooser();
    const onSelectFile = file => {
      chooserUiClose();
      if (file.length) {
        let minute = parseInt(file.length / 60);
        let second = Math.round(file.length % 60);
        $("#minute").val(minute);
        $("#second").val(second);
        $("#length").val(minute * 60 + second);
      }
      $('[name="ext[mediaId]"]').val(file.source);
      if (file.source == 'self') {
        $("#ext_mediaId").val(file.id);
        $("#ext_mediaUri").val('');
      } else {
        $("#ext_mediaId").val('');
        $("#ext_mediaUri").val(file.uri);
      }
    }
    fileChooser.on('select', onSelectFile);
  }
}
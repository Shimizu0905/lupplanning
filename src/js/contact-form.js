const form = document.querySelector('.js-contact-form');

if (form) {
  const submitBtn = form.querySelector('.js-submit-btn');

  // Bot対策: フォーム読み込み時刻を記録
  const loadedAtField = form.querySelector('input[name="_form_loaded_at"]');
  if (loadedAtField) {
    loadedAtField.value = Date.now();
  }

  // バリデーションルール
  const validators = {
    inquiry_type: {
      validate: () => !!form.querySelector('input[name="inquiry_type"]:checked'),
      message: 'お問い合わせ内容を選択してください。',
    },
    name: {
      validate: () => form.querySelector('[name="name"]').value.trim() !== '',
      message: 'お名前を入力してください。',
    },
    kana: {
      validate: () => {
        const val = form.querySelector('[name="kana"]').value.trim();
        if (!val) return false;
        return /^[\u30A0-\u30FF\u3000\s　]+$/.test(val);
      },
      message: 'フリガナを全角カタカナで入力してください。',
    },
    tel: {
      validate: () => {
        const val = form.querySelector('[name="tel"]').value.trim();
        if (!val) return false;
        const normalized = val
          .replace(/[\uff10-\uff19]/g, (s) => String.fromCharCode(s.charCodeAt(0) - 0xfee0))
          .replace(/[^\d]/g, '');
        return normalized.length >= 10 && normalized.length <= 11;
      },
      message: '電話番号を正しく入力してください（10〜11桁）。',
    },
    email: {
      validate: () => {
        const val = form.querySelector('[name="email"]').value.trim();
        if (!val) return false;
        return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(val);
      },
      message: 'メールアドレスの形式が正しくありません。',
    },
    address: {
      validate: () => form.querySelector('[name="address"]').value.trim() !== '',
      message: '設置先ご住所を入力してください。',
    },
    privacy: {
      validate: () => {
        const cb = form.querySelector('[name="privacy"]');
        return cb && cb.checked;
      },
      message: 'プライバシーポリシーに同意してください。',
    },
  };

  const showError = (fieldName, message) => {
    const errorEl = form.querySelector(`.js-error-${fieldName}`);
    if (errorEl) {
      errorEl.textContent = message;
    }
    // 入力要素にエラースタイル付与
    const input = form.querySelector(`[name="${fieldName}"]`);
    if (input) {
      input.classList.add('is-error');
    }
    // radiogroup の場合はラジオグループ全体
    if (fieldName === 'inquiry_type') {
      const radios = form.querySelector('.p-contact__form-radios');
      if (radios) radios.classList.add('is-error');
    }
  };

  const clearError = (fieldName) => {
    const errorEl = form.querySelector(`.js-error-${fieldName}`);
    if (errorEl) {
      errorEl.textContent = '';
    }
    const input = form.querySelector(`[name="${fieldName}"]`);
    if (input) {
      input.classList.remove('is-error');
    }
    if (fieldName === 'inquiry_type') {
      const radios = form.querySelector('.p-contact__form-radios');
      if (radios) radios.classList.remove('is-error');
    }
  };

  const validateAll = () => {
    let isValid = true;
    let firstErrorField = null;

    for (const [fieldName, rule] of Object.entries(validators)) {
      if (!rule.validate()) {
        showError(fieldName, rule.message);
        isValid = false;
        if (!firstErrorField) {
          firstErrorField = fieldName;
        }
      } else {
        clearError(fieldName);
      }
    }

    // 最初のエラー項目にスクロール
    if (firstErrorField) {
      const target =
        firstErrorField === 'inquiry_type'
          ? form.querySelector('.p-contact__form-radios')
          : form.querySelector(`[name="${firstErrorField}"]`);
      if (target) {
        target.scrollIntoView({ behavior: 'smooth', block: 'center' });
        if (target.focus) target.focus({ preventScroll: true });
      }
    }

    return isValid;
  };

  // リアルタイムバリデーション（blur時）
  const textFields = ['name', 'kana', 'tel', 'email', 'address'];
  textFields.forEach((fieldName) => {
    const input = form.querySelector(`[name="${fieldName}"]`);
    if (!input) return;

    input.addEventListener('blur', () => {
      // 一度も送信試行していない場合はblurバリデーションしない
      if (!form.dataset.submitted) return;

      const rule = validators[fieldName];
      if (rule && !rule.validate()) {
        showError(fieldName, rule.message);
      } else {
        clearError(fieldName);
      }
    });
  });

  // ラジオボタンの変更時
  form.querySelectorAll('input[name="inquiry_type"]').forEach((radio) => {
    radio.addEventListener('change', () => {
      if (form.dataset.submitted) {
        clearError('inquiry_type');
      }
    });
  });

  // プライバシーチェックボックスの変更時
  const privacyCb = form.querySelector('[name="privacy"]');
  if (privacyCb) {
    privacyCb.addEventListener('change', () => {
      if (form.dataset.submitted) {
        if (privacyCb.checked) {
          clearError('privacy');
        } else {
          showError('privacy', validators.privacy.message);
        }
      }
    });
  }

  // フォーム送信
  form.addEventListener('submit', (e) => {
    form.dataset.submitted = 'true';

    if (!validateAll()) {
      e.preventDefault();
      return;
    }

    // 二重送信防止
    submitBtn.disabled = true;
    submitBtn.textContent = '送信中...';
    submitBtn.classList.add('is-loading');
  });
}

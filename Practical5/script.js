const form = document.getElementById('registrationForm');
const fullName = document.getElementById('fullName');
const email = document.getElementById('email');
const mobile = document.getElementById('mobile');
const password = document.getElementById('password');
const confirmPassword = document.getElementById('confirmPassword');
const course = document.getElementById('course');
const year = document.getElementById('year');
const terms = document.getElementById('terms');

const setError = (field, message) => {
  const errorElement = document.getElementById(`${field.id}Error`);
  if (errorElement) {
    errorElement.textContent = message;
  }
  field.classList.add('invalid');
};

const clearError = (field) => {
  const errorElement = document.getElementById(`${field.id}Error`);
  if (errorElement) {
    errorElement.textContent = '';
  }
  field.classList.remove('invalid');
};

const validateName = () => {
  const value = fullName.value.trim();
  const pattern = /^[A-Za-z ]{3,50}$/;

  if (!value) {
    setError(fullName, 'Name is required.');
    return false;
  }

  if (!pattern.test(value)) {
    setError(fullName, 'Name must contain only letters and spaces (3-50 chars).');
    return false;
  }

  clearError(fullName);
  return true;
};

const validateEmail = () => {
  const value = email.value.trim();
  const pattern = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;

  if (!value) {
    setError(email, 'Email is required.');
    return false;
  }

  if (!pattern.test(value)) {
    setError(email, 'Please enter a valid email address.');
    return false;
  }

  clearError(email);
  return true;
};

const validateMobile = () => {
  const value = mobile.value.trim();
  const pattern = /^[6-9]\d{9}$/;

  if (!value) {
    setError(mobile, 'Mobile number is required.');
    return false;
  }

  if (!pattern.test(value)) {
    setError(mobile, 'Mobile number must be 10 digits starting with 6-9.');
    return false;
  }

  clearError(mobile);
  return true;
};

const getPasswordStrength = (value) => {
  let score = 0;

  if (value.length >= 8) score += 1;
  if (/[A-Z]/.test(value)) score += 1;
  if (/[0-9]/.test(value)) score += 1;
  if (/[^A-Za-z0-9]/.test(value)) score += 1;

  if (value.length === 0) return { score: 0, label: 'Password strength: -', color: '#d1d5db' };
  if (score <= 1) return { score: 1, label: 'Password strength: Weak', color: '#ef4444' };
  if (score === 2) return { score: 2, label: 'Password strength: Medium', color: '#f59e0b' };
  if (score >= 3) return { score: 3, label: 'Password strength: Strong', color: '#22c55e' };

  return { score: 0, label: 'Password strength: -', color: '#d1d5db' };
};

const updatePasswordStrength = () => {
  const value = password.value;
  const strength = getPasswordStrength(value);
  const bar = document.getElementById('strengthBar');
  const text = document.getElementById('strengthText');

  const widths = ['0%', '33%', '66%', '100%'];
  bar.style.width = widths[strength.score];
  bar.style.background = strength.color;
  text.textContent = strength.label;
  text.style.color = strength.color;
};

const validatePassword = () => {
  const value = password.value;
  const pattern = /^(?=.*[A-Z])(?=.*\d)(?=.*[^A-Za-z0-9]).{8,}$/;

  if (!value) {
    setError(password, 'Password is required.');
    return false;
  }

  if (!pattern.test(value)) {
    setError(password, 'Password must be at least 8 chars, with 1 uppercase, 1 number, and 1 special character.');
    return false;
  }

  clearError(password);
  return true;
};

const validateConfirmPassword = () => {
  const value = confirmPassword.value;

  if (!value) {
    setError(confirmPassword, 'Please confirm your password.');
    return false;
  }

  if (value !== password.value) {
    setError(confirmPassword, 'Passwords do not match.');
    return false;
  }

  clearError(confirmPassword);
  return true;
};

const validateCourse = () => {
  if (!course.value) {
    setError(course, 'Please select a course.');
    return false;
  }

  clearError(course);
  return true;
};

const validateYear = () => {
  if (!year.value) {
    setError(year, 'Please select your year.');
    return false;
  }

  clearError(year);
  return true;
};

const validateGender = () => {
  const selected = document.querySelector('input[name="gender"]:checked');

  if (!selected) {
    document.getElementById('genderError').textContent = 'Please select a gender.';
    return false;
  }

  document.getElementById('genderError').textContent = '';
  return true;
};

const validateTerms = () => {
  if (!terms.checked) {
    document.getElementById('termsError').textContent = 'You must accept the terms and conditions.';
    return false;
  }

  document.getElementById('termsError').textContent = '';
  return true;
};

const validateForm = () => {
  const checks = [
    validateName(),
    validateEmail(),
    validateMobile(),
    validatePassword(),
    validateConfirmPassword(),
    validateCourse(),
    validateYear(),
    validateGender(),
    validateTerms()
  ];

  return checks.every(Boolean);
};

fullName.addEventListener('input', validateName);
email.addEventListener('input', validateEmail);
mobile.addEventListener('input', validateMobile);
password.addEventListener('input', () => {
  updatePasswordStrength();
  if (confirmPassword.value) {
    validateConfirmPassword();
  }
  if (password.value) {
    validatePassword();
  }
});
confirmPassword.addEventListener('input', validateConfirmPassword);
course.addEventListener('change', validateCourse);
year.addEventListener('change', validateYear);
terms.addEventListener('change', validateTerms);
document.querySelectorAll('input[name="gender"]').forEach((radio) => {
  radio.addEventListener('change', validateGender);
});

form.addEventListener('submit', (event) => {
  event.preventDefault();

  if (validateForm()) {
    const submitButton = form.querySelector('button[type="submit"]');
    submitButton.disabled = true;
    submitButton.textContent = 'Registered Successfully ✓';
    alert('Registration successful!');
  }
});

updatePasswordStrength();

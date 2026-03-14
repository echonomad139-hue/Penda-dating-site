import { useState } from 'react';
import { useNavigate, Link } from 'react-router-dom';
import { useFormik } from 'formik';
import * as Yup from 'yup';
import { Mail, Lock, Eye, EyeOff, KeyRound } from 'lucide-react';
import Input from '../components/UI/Input';
import Button from '../components/UI/Button';
import useAuthStore from '../store/authSlice';
import { OTP_DELIVERY_METHODS } from '../config';
import toast from 'react-hot-toast';
import './ForgotPasswordPage.css';

const STEPS = {
  REQUEST_OTP: 'REQUEST_OTP',
  VERIFY_OTP: 'VERIFY_OTP',
  RESET_PASSWORD: 'RESET_PASSWORD'
};

const requestOtpSchema = Yup.object({
  email: Yup.string().email('Enter a valid email').required('Email is required'),
});

const verifyOtpSchema = Yup.object({
  otp: Yup.string().length(6, 'OTP must be 6 digits').required('OTP is required'),
});

const resetPasswordSchema = Yup.object({
  newPassword: Yup.string().min(6, 'At least 6 characters').required('Password is required'),
  confirmPassword: Yup.string()
    .oneOf([Yup.ref('newPassword'), null], 'Passwords must match')
    .required('Confirm your password'),
});

export default function ForgotPasswordPage() {
  const navigate = useNavigate();
  const { requestOTP, verifyOTP, resetPassword, isLoading } = useAuthStore();
  const [currentStep, setCurrentStep] = useState(STEPS.REQUEST_OTP);
  const [email, setEmail] = useState('');
  const [otpCode, setOtpCode] = useState('');
  const [showPass, setShowPass] = useState(false);
  const [showConfirmPass, setShowConfirmPass] = useState(false);
  const [deliveryMethod, setDeliveryMethod] = useState('email');

  // Formik for Request OTP
  const requestFormik = useFormik({
    initialValues: { email: '' },
    validationSchema: requestOtpSchema,
    onSubmit: async (values) => {
      try {
        await requestOTP(values.email, deliveryMethod);
        setEmail(values.email);
        const methodLabel = OTP_DELIVERY_METHODS.find(m => m.id === deliveryMethod)?.label || deliveryMethod;
        toast.success(`OTP sent via ${methodLabel}`);
        setCurrentStep(STEPS.VERIFY_OTP);
      } catch (error) {
        toast.error(error.message || 'Failed to send OTP');
      }
    },
  });

  // Formik for Verify OTP
  const verifyFormik = useFormik({
    initialValues: { otp: '' },
    validationSchema: verifyOtpSchema,
    onSubmit: async (values) => {
      try {
        await verifyOTP(email, values.otp);
        setOtpCode(values.otp);
        toast.success('OTP verified');
        setCurrentStep(STEPS.RESET_PASSWORD);
      } catch (error) {
        toast.error(error.message || 'Invalid OTP');
      }
    },
  });

  // Formik for Reset Password
  const resetFormik = useFormik({
    initialValues: { newPassword: '', confirmPassword: '' },
    validationSchema: resetPasswordSchema,
    onSubmit: async (values) => {
      try {
        await resetPassword(email, otpCode, values.newPassword);
        toast.success('Password reset successfully');
        navigate('/login');
      } catch (error) {
        toast.error(error.message || 'Failed to reset password');
      }
    },
  });

  return (
    <div className="forgot-password-page">
      <div className="forgot-password-page__header">
        <div className="forgot-password-page__logo">P</div>
        <h1 className="forgot-password-page__title">
          {currentStep === STEPS.REQUEST_OTP && 'Forgot Password'}
          {currentStep === STEPS.VERIFY_OTP && 'Verify OTP'}
          {currentStep === STEPS.RESET_PASSWORD && 'Reset Password'}
        </h1>
        <p className="forgot-password-page__subtitle">
          {currentStep === STEPS.REQUEST_OTP && "Enter your email to reset your password."}
          {currentStep === STEPS.VERIFY_OTP && `Enter the 6-digit code sent to ${email}.`}
          {currentStep === STEPS.RESET_PASSWORD && "Create a new strong password."}
        </p>
      </div>

      {currentStep === STEPS.REQUEST_OTP && (
        <form className="forgot-password-page__form" onSubmit={requestFormik.handleSubmit}>
          <Input
            label="Email"
            name="email"
            type="email"
            placeholder="your@email.com"
            value={requestFormik.values.email}
            onChange={requestFormik.handleChange}
            onBlur={requestFormik.handleBlur}
            error={requestFormik.errors.email}
            touched={requestFormik.touched.email}
            icon={<Mail size={18} />}
          />
          <div className="forgot-password-page__delivery">
            <label className="forgot-password-page__delivery-label">Send OTP via</label>
            <div className="forgot-password-page__delivery-options">
              {OTP_DELIVERY_METHODS.map((method) => (
                <button
                  key={method.id}
                  type="button"
                  className={`forgot-password-page__delivery-btn ${deliveryMethod === method.id ? 'active' : ''}`}
                  onClick={() => setDeliveryMethod(method.id)}
                >
                  <span>{method.emoji}</span>
                  <span>{method.label}</span>
                </button>
              ))}
            </div>
          </div>
          <Button type="submit" fullWidth loading={isLoading}>
            Send OTP
          </Button>
        </form>
      )}

      {currentStep === STEPS.VERIFY_OTP && (
        <form className="forgot-password-page__form" onSubmit={verifyFormik.handleSubmit}>
          <Input
            label="OTP Code"
            name="otp"
            type="text"
            placeholder="123456"
            value={verifyFormik.values.otp}
            onChange={verifyFormik.handleChange}
            onBlur={verifyFormik.handleBlur}
            error={verifyFormik.errors.otp}
            touched={verifyFormik.touched.otp}
            icon={<KeyRound size={18} />}
          />
          <Button type="submit" fullWidth loading={isLoading}>
            Verify Code
          </Button>
        </form>
      )}

      {currentStep === STEPS.RESET_PASSWORD && (
        <form className="forgot-password-page__form" onSubmit={resetFormik.handleSubmit}>
          <div className="forgot-password-page__pass-wrapper">
            <Input
              label="New Password"
              name="newPassword"
              type={showPass ? 'text' : 'password'}
              placeholder="Enter new password"
              value={resetFormik.values.newPassword}
              onChange={resetFormik.handleChange}
              onBlur={resetFormik.handleBlur}
              error={resetFormik.errors.newPassword}
              touched={resetFormik.touched.newPassword}
              icon={<Lock size={18} />}
            />
            <button
              type="button"
              className="forgot-password-page__eye"
              onClick={() => setShowPass(!showPass)}
              aria-label="Toggle password visibility"
            >
              {showPass ? <EyeOff size={18} /> : <Eye size={18} />}
            </button>
          </div>

          <div className="forgot-password-page__pass-wrapper">
            <Input
              label="Confirm Password"
              name="confirmPassword"
              type={showConfirmPass ? 'text' : 'password'}
              placeholder="Confirm new password"
              value={resetFormik.values.confirmPassword}
              onChange={resetFormik.handleChange}
              onBlur={resetFormik.handleBlur}
              error={resetFormik.errors.confirmPassword}
              touched={resetFormik.touched.confirmPassword}
              icon={<Lock size={18} />}
            />
            <button
              type="button"
              className="forgot-password-page__eye"
              onClick={() => setShowConfirmPass(!showConfirmPass)}
              aria-label="Toggle password visibility"
            >
              {showConfirmPass ? <EyeOff size={18} /> : <Eye size={18} />}
            </button>
          </div>

          <Button type="submit" fullWidth loading={isLoading}>
            Reset Password
          </Button>
        </form>
      )}

      <div className="forgot-password-page__footer">
        <p>
          Remember your password?{' '}
          <Link to="/signin" className="forgot-password-page__link">
            Sign in
          </Link>
        </p>
      </div>
    </div>
  );
}

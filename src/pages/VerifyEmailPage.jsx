import { useState } from 'react';
import { useNavigate, Link } from 'react-router-dom';
import { useFormik } from 'formik';
import * as Yup from 'yup';
import { KeyRound, MailCheck, ArrowLeft } from 'lucide-react';
import Input from '../components/UI/Input';
import Button from '../components/UI/Button';
import useAuthStore from '../store/authSlice';
import { OTP_DELIVERY_METHODS } from '../config';
import toast from 'react-hot-toast';
import './VerifyEmailPage.css';

const otpSchema = Yup.object({
  otp: Yup.string()
    .matches(/^\d{6}$/, 'OTP must be exactly 6 digits')
    .required('OTP is required'),
});

export default function VerifyEmailPage() {
  const navigate = useNavigate();
  const {
    pendingRegistration,
    verifyRegistrationOTP,
    requestRegistrationOTP,
    register,
    isLoading,
  } = useAuthStore();
  const [resending, setResending] = useState(false);

  const email = pendingRegistration?.email || '';
  const deliveryMethod = pendingRegistration?.deliveryMethod || 'email';
  const methodLabel = OTP_DELIVERY_METHODS.find(m => m.id === deliveryMethod)?.label || deliveryMethod;

  const formik = useFormik({
    initialValues: { otp: '' },
    validationSchema: otpSchema,
    onSubmit: async (values) => {
      if (!pendingRegistration) return;
      try {
        await verifyRegistrationOTP(email, values.otp);
        toast.success('Email verified! Creating your account…');

        // Complete registration with the stored form data
        await register({
          ...pendingRegistration,
          user_type: 'normal',
          display_name: pendingRegistration.name,
          relationship_intent: pendingRegistration.intention,
          latitude: null,
          longitude: null,
        });

        navigate('/profile-setup');
      } catch (error) {
        toast.error(error.message || 'Invalid OTP. Please try again.');
      }
    },
  });

  const handleResend = async () => {
    setResending(true);
    try {
      await requestRegistrationOTP(email, deliveryMethod);
      toast.success(`A new OTP has been sent via ${methodLabel}`);
    } catch (error) {
      toast.error(error.message || 'Failed to resend OTP');
    } finally {
      setResending(false);
    }
  };

  // No pending registration — show a fallback message
  if (!pendingRegistration) {
    return (
      <div className="verify-email-page">
        <div className="verify-email-page__header">
          <div className="verify-email-page__icon-ring verify-email-page__icon-ring--warn">
            <MailCheck size={28} />
          </div>
          <h1 className="verify-email-page__title">No Pending Verification</h1>
          <p className="verify-email-page__subtitle">
            Please sign up first to verify your email.
          </p>
        </div>
        <div className="verify-email-page__footer">
          <Link to="/signup" className="verify-email-page__link">
            ← Back to Sign Up
          </Link>
        </div>
      </div>
    );
  }

  // Mask the email for display
  const maskedEmail = email.replace(/(.{2})(.*)(@.*)/, '$1***$3');

  return (
    <div className="verify-email-page">
      <div className="verify-email-page__header">
        <div className="verify-email-page__icon-ring">
          <MailCheck size={28} />
        </div>
        <h1 className="verify-email-page__title">Verify Your Email</h1>
        <p className="verify-email-page__subtitle">
          We sent a 6-digit code to <strong>{maskedEmail}</strong> via <strong>{methodLabel}</strong>
        </p>
      </div>

      <form className="verify-email-page__form" onSubmit={formik.handleSubmit}>
        <Input
          label="Verification Code"
          name="otp"
          type="text"
          placeholder="000000"
          maxLength={6}
          value={formik.values.otp}
          onChange={formik.handleChange}
          onBlur={formik.handleBlur}
          error={formik.errors.otp}
          touched={formik.touched.otp}
          icon={<KeyRound size={18} />}
        />
        <Button type="submit" fullWidth loading={isLoading}>
          Verify & Create Account
        </Button>
      </form>

      <div className="verify-email-page__actions">
        <button
          type="button"
          className="verify-email-page__resend-btn"
          onClick={handleResend}
          disabled={resending}
        >
          {resending ? 'Sending…' : "Didn't get the code? Resend"}
        </button>
      </div>

      <div className="verify-email-page__footer">
        <Link to="/signup" className="verify-email-page__back-link">
          <ArrowLeft size={14} />
          <span>Back to Sign Up</span>
        </Link>
      </div>
    </div>
  );
}

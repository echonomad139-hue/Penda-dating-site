import { useState } from 'react';
import { useNavigate, Link } from 'react-router-dom';
import { useFormik } from 'formik';
import * as Yup from 'yup';
import { Mail, Lock, Eye, EyeOff } from 'lucide-react';
import Input from '../components/UI/Input';
import Button from '../components/UI/Button';
import useAuthStore from '../store/authSlice';
import './SignInPage.css';

const loginSchema = Yup.object({
  email: Yup.string().email('Enter a valid email').required('Email is required'),
  password: Yup.string().min(6, 'At least 6 characters').required('Password is required'),
});

export default function SignInPage() {
  const navigate = useNavigate();
  const { login, isLoading } = useAuthStore();
  const [showPass, setShowPass] = useState(false);

  const formik = useFormik({
    initialValues: { email: '', password: '' },
    validationSchema: loginSchema,
    onSubmit: async (values) => {
      await login(values);
      navigate('/discover');
    },
  });

  return (
    <div className="signin-page">
      <div className="signin-page__header">
        <div className="signin-page__logo">P</div>
        <h1 className="signin-page__title">Welcome back</h1>
        <p className="signin-page__subtitle">Continue your journey</p>
      </div>

      <form className="signin-page__form" onSubmit={formik.handleSubmit}>
        <Input
          label="Email"
          name="email"
          type="email"
          placeholder="your@email.com"
          value={formik.values.email}
          onChange={formik.handleChange}
          onBlur={formik.handleBlur}
          error={formik.errors.email}
          touched={formik.touched.email}
          icon={<Mail size={18} />}
        />

        <div className="signin-page__pass-wrapper">
          <Input
            label="Password"
            name="password"
            type={showPass ? 'text' : 'password'}
            placeholder="Enter your password"
            value={formik.values.password}
            onChange={formik.handleChange}
            onBlur={formik.handleBlur}
            error={formik.errors.password}
            touched={formik.touched.password}
            icon={<Lock size={18} />}
          />
          <button
            type="button"
            className="signin-page__eye"
            onClick={() => setShowPass(!showPass)}
            aria-label="Toggle password visibility"
          >
            {showPass ? <EyeOff size={18} /> : <Eye size={18} />}
          </button>
        </div>

        <Button type="submit" fullWidth loading={isLoading}>
          Sign In
        </Button>
      </form>

      <div className="signin-page__footer">
        <p>
          New to PENDA?{' '}
          <Link to="/signup" className="signin-page__link">
            Create an account
          </Link>
        </p>
      </div>
    </div>
  );
}
